<?php

namespace App\Repositories;

use App\Models\Image;

class ImageRepository implements GlobalInterface
{
    public function all()
    {
    }

    public function get($id)
    {
    }

    public function image($tname, $tid)
    {
        return Image::where('table_name', $tname)
            ->where('table_id', $tid)
            ->whereNull('file_type')
            ->get();
    }

    public function file($tname, $tid)
    {
        return Image::where('table_name', $tname)
            ->where('table_id', $tid)
            ->whereNotNull('file_type')
            ->get();
    }

    public function store(array $data)
    {
        $data['created_by'] = auth()->id();
        $store = Image::create($data);

        return $store->image_id;
    }

    public function update($id, array $data)
    {
    }

    public function delete($id)
    {
        Image::destroy($id);
    }

    public function delete2($tname, $tid)
    {
        // All Delete Not Used
        Image::where('table_name', $tname)
            ->where('table_id', $tid)
            ->delete();
    }

    /**
     * Upload folder (under public/resources) of each images.table_name; product files
     * (file_type set) are kept apart from product images.
     */
    public const FOLDERS = [
        'customers' => 'customer',
        'employees' => 'employee',
        'stocks' => 'issuance',
        'machines' => 'machine',
        'materials' => 'material',
        'products' => 'product',
        'transactions' => 'transaction',
        'vendors' => 'vendor',
    ];

    public function folderFor(Image $image): ?string
    {
        if ($image->table_name === 'products' && ! empty($image->file_type)) {
            return 'product_file';
        }

        return self::FOLDERS[$image->table_name] ?? null;
    }

    public function countFor(string $table, $ids): int
    {
        return Image::where('table_name', $table)->whereIn('table_id', (array) $ids)->count();
    }

    /**
     * Deletes one image / file row and its file.
     */
    public function deleteWithFile(Image $image): void
    {
        $folder = $this->folderFor($image);
        $image->delete();
        if ($folder && $image->image !== '' && basename($image->image) === $image->image) {
            $path = public_path('resources/'.$folder.'/'.$image->image);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Deletes every image / file of the given records (used when the records themselves are deleted).
     */
    public function deleteFor(string $table, $ids): int
    {
        $images = Image::where('table_name', $table)->whereIn('table_id', (array) $ids)->get();
        foreach ($images as $image) {
            $this->deleteWithFile($image);
        }

        return $images->count();
    }
}
