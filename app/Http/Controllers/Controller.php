<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeletion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Support\Uploads;

class Controller extends BaseController
{
    use AuthorizesRequests, HandlesDeletion, ValidatesRequests;

    /**
     * Passes when the user may perform ANY of the given abilities, e.g.
     * authorizeAny([['show', Stock::class], ['reports_access', User::class]]) for a report that
     * belongs to both the Stock and the Reports menu.
     *
     * @param  array<int, array{0: string, 1: mixed}>  $abilities
     */
    protected function authorizeAny(array $abilities): void
    {
        $user = auth()->user();
        foreach ($abilities as [$ability, $arguments]) {
            if ($user && $user->can($ability, $arguments)) {
                return;
            }
        }

        throw new AuthorizationException('This action is unauthorized.');
    }

    protected function storeImage($image, $folder, $table, $tableId)
    {
        $fileNameToStore = Uploads::store($image, $folder, Uploads::IMAGE_EXTENSIONS, Uploads::IMAGE_MAX_KB, 'image');
        $store = ['table_name' => $table, 'table_id' => $tableId, 'image' => $fileNameToStore];

        return $this->imageRepository->store($store);
    }

    protected function storeFile($image, $folder, $table, $tableId, $title)
    {
        $fileNameToStore = Uploads::store($image, $folder, Uploads::PRODUCT_FILE_EXTENSIONS, Uploads::PRODUCT_FILE_MAX_KB, 'file');
        $store = ['table_name' => $table, 'table_id' => $tableId, 'image' => $fileNameToStore, 'file_title' => $title, 'file_type' => '1'];

        return $this->imageRepository->store($store);
    }
}
