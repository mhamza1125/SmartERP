<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Support\Uploads;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * F-01 / F-26: uploads are validated, never stored under the client's name or extension,
 * and SVG / scripts / renamed / double-extension / oversized files are refused.
 */
class UploadSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private const FOLDER = 'zz_upload_test';

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('resources/'.self::FOLDER));
        parent::tearDown();
    }

    /** A real upload (content sniffed by fileinfo, like a browser upload); fake() files only look at the name. */
    private function upload(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function png(): string
    {
        ob_start();
        imagepng(imagecreatetruecolor(4, 4));

        return ob_get_clean();
    }

    private function refusedAsImage(UploadedFile $file): bool
    {
        try {
            Uploads::store($file, self::FOLDER, Uploads::IMAGE_EXTENSIONS, Uploads::IMAGE_MAX_KB, 'image');
        } catch (ValidationException) {
            return true;
        }

        return false;
    }

    private function refusedAsProductFile(UploadedFile $file): bool
    {
        try {
            Uploads::store($file, self::FOLDER, Uploads::PRODUCT_FILE_EXTENSIONS, Uploads::PRODUCT_FILE_MAX_KB);
        } catch (ValidationException) {
            return true;
        }

        return false;
    }

    public function test_php_script_is_refused(): void
    {
        $this->assertTrue($this->refusedAsProductFile($this->upload('x.php', '<?php echo 1;')));
        $this->assertTrue($this->refusedAsImage($this->upload('x.phtml', '<?php echo 1;')));
        $this->assertDirectoryDoesNotExist(public_path('resources/'.self::FOLDER));
    }

    public function test_script_renamed_to_an_allowed_extension_is_refused(): void
    {
        $this->assertTrue($this->refusedAsImage($this->upload('x.jpg', '<?php echo 1;')));
        $this->assertTrue($this->refusedAsProductFile($this->upload('x.pdf', '<?php echo 1;')));
    }

    public function test_double_extensions_are_refused(): void
    {
        $this->assertTrue($this->refusedAsImage($this->upload('x.php.png', $this->png())), 'inner .php');
        $this->assertTrue($this->refusedAsImage($this->upload('x.png.php', $this->png())), 'outer .php');
        $this->assertTrue($this->refusedAsProductFile($this->upload('x.phtml.jpg', $this->png())), 'inner .phtml');
    }

    public function test_svg_is_refused_as_an_image(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';
        $this->assertTrue($this->refusedAsImage($this->upload('logo.svg', $svg)));
    }

    public function test_oversized_files_are_refused(): void
    {
        $this->assertTrue($this->refusedAsImage(UploadedFile::fake()->image('big.png')->size(Uploads::IMAGE_MAX_KB + 1)));
        $this->assertTrue($this->refusedAsProductFile(UploadedFile::fake()->image('big.png')->size(Uploads::PRODUCT_FILE_MAX_KB + 1)));
    }

    public function test_stored_name_and_extension_are_chosen_by_the_server(): void
    {
        // PNG content uploaded under a .jpg name: kept, but stored as .png with a random name
        $stored = Uploads::store($this->upload('My Photo.jpg', $this->png()), self::FOLDER, Uploads::IMAGE_EXTENSIONS, Uploads::IMAGE_MAX_KB);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{32}\.png$/', $stored);
        $this->assertStringNotContainsString('Photo', $stored);
        $this->assertFileExists(public_path('resources/'.self::FOLDER.'/'.$stored));

        $jpeg = Uploads::store(UploadedFile::fake()->image('a.jpeg'), self::FOLDER, Uploads::IMAGE_EXTENSIONS, Uploads::IMAGE_MAX_KB);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{32}\.jpg$/', $jpeg);
        $this->assertNotSame($stored, $jpeg);
    }

    public function test_real_spreadsheet_is_accepted_as_a_product_file(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'xl').'.xlsx';
        (new Xlsx(new Spreadsheet()))->save($path);
        $file = new UploadedFile($path, 'Price Sheet.xlsx', null, null, true);

        $stored = Uploads::store($file, self::FOLDER, Uploads::PRODUCT_FILE_EXTENSIONS, Uploads::PRODUCT_FILE_MAX_KB);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{32}\.xlsx$/', $stored);
    }

    public function test_product_form_refuses_a_script_before_saving_anything(): void
    {
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->firstOrFail());
        $before = Product::count();
        $images = DB::table('images')->count();

        $this->post(route('product.store'), [
            'category_id' => 1, 'article_no' => 'ZZ-UPLOAD-'.uniqid(), 'name' => 'Upload test',
            'unit_id' => 1, 'product_status' => 1,
            'file' => [$this->upload('shell.php', '<?php system($_GET["c"]);')],
            'file_title' => ['x'],
        ])->assertSessionHasErrors('file.0');

        $this->assertSame($before, Product::count());
        $this->assertSame($images, DB::table('images')->count());
        $this->assertDirectoryDoesNotExist(public_path('resources/product_file'));
    }

    public function test_image_form_requests_refuse_svg(): void
    {
        $this->actingAs(User::where('role_id', User::ROLE_ADMIN)->firstOrFail());
        $svg = $this->upload('a.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

        $this->post(route('vendor.store'), ['image' => [$svg]])->assertSessionHasErrors('image.0');
        $this->post(route('customer.store'), ['image' => [$svg]])->assertSessionHasErrors('image.0');
    }
}
