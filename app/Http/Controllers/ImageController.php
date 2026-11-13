<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Repositories\ImageRepository;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    protected $imageRepository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->middleware(['auth', 'all']);
        $this->imageRepository = $imageRepository;
    }

    public function index()
    {
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show(Image $image)
    {
    }

    public function edit(Image $image)
    {
    }

    public function update(Request $request, Image $image)
    {
    }

    /**
     * Images / files belong to a record of another module: deleting one needs edit permission on
     * that module. The file location comes from the image row, never from the URL.
     */
    private const OWNER_POLICIES = [
        'customers' => \App\Models\Customer::class,
        'employees' => \App\Models\Employee::class,
        'vendors' => \App\Models\Vendor::class,
        'materials' => \App\Models\Material::class,
        'machines' => \App\Models\Machine::class,
        'products' => \App\Models\Product::class,
        'stocks' => \App\Models\Stock::class,
        'transactions' => \App\Models\Transaction::class,
    ];

    public function destroy(Image $id, $dir)
    {
        $model = self::OWNER_POLICIES[$id->table_name] ?? null;
        abort_if($model === null, 403);
        if ($id->table_name === 'vendors' && (string) \App\Models\Vendor::where('vendor_id', $id->table_id)->value('vendor_type') === '1') {
            $this->authorize('contractors_edit', $model);
        } else {
            $this->authorize('edit', $model);
        }

        $isFile = ! empty($id->file_type);
        $this->imageRepository->deleteWithFile($id);

        return back()->with('success', $isFile ? 'File Deleted Successfully' : 'Image Deleted Successfully');
    }
}
