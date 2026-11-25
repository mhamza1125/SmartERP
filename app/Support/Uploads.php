<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * One place for what may be uploaded and how it is stored.
 *
 * Uploaded files live under public/resources/{folder}. They are never stored under the
 * client's name or extension: the name is random and the extension comes from the file's
 * real content (and must be on the allow-list). public/resources/.htaccess refuses scripts.
 */
class Uploads
{
    /** Pictures (records' image galleries). No SVG: it can carry script. */
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    /** Product documents (drawings, spec sheets, price sheets). */
    public const PRODUCT_FILE_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx'];

    /** Max size in KB. */
    public const IMAGE_MAX_KB = 5120;

    public const PRODUCT_FILE_MAX_KB = 10240;

    /** Validation rules for one uploaded image (use as 'image.*'). */
    public static function imageRules(): array
    {
        return self::rules(self::IMAGE_EXTENSIONS, self::IMAGE_MAX_KB);
    }

    /** Validation rules for one uploaded product file (use as 'file.*'). */
    public static function productFileRules(): array
    {
        return self::rules(self::PRODUCT_FILE_EXTENSIONS, self::PRODUCT_FILE_MAX_KB);
    }

    /**
     * Validates the file again (defence in depth) and moves it to public/resources/{folder}
     * under a random name with the extension of its real content. Returns the stored name.
     *
     * @throws ValidationException
     */
    public static function store(UploadedFile $file, string $folder, array $allowed, int $maxKb, string $field = 'file'): string
    {
        $validator = validator([$field => $file], [$field => self::rules($allowed, $maxKb)]);
        $validator->validate();

        $extension = self::contentExtension($file);
        if ($extension === null || ! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([$field => 'This file type is not allowed.']);
        }

        $name = Str::lower(Str::random(32)).'.'.$extension;
        $file->move(public_path('resources/'.$folder), $name);

        return $name;
    }

    /** Extension from the file's content (jpeg is stored as jpg). */
    private static function contentExtension(UploadedFile $file): ?string
    {
        $extension = $file->guessExtension();

        return $extension === 'jpeg' ? 'jpg' : ($extension ? Str::lower($extension) : null);
    }

    private static function rules(array $allowed, int $maxKb): array
    {
        return [
            'file',
            'max:'.$maxKb,
            'mimes:'.implode(',', $allowed),
            // The client's extension must be on the list too, so a renamed file
            // (x.php.png content-sniffed as png but named .php) is refused outright.
            function (string $attribute, $value, \Closure $fail) use ($allowed) {
                $name = $value instanceof UploadedFile ? $value->getClientOriginalName() : '';
                $parts = explode('.', Str::lower($name));
                $last = count($parts) > 1 ? end($parts) : '';
                if (! in_array($last, $allowed, true)) {
                    $fail('The file must be one of: '.implode(', ', $allowed).'.');

                    return;
                }
                // Double extensions with an executable inner part (x.php.jpg).
                foreach (array_slice($parts, 1, -1) as $inner) {
                    if (preg_match('/^(php\d*|phtml|phar|pht|phps|cgi|pl|py|sh|asp|aspx|jsp|shtml|htaccess|exe|bat|cmd|js|svg|html?)$/', $inner)) {
                        $fail('The file name may not contain an executable extension.');

                        return;
                    }
                }
            },
        ];
    }
}
