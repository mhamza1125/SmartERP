<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesDeletion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

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

    protected function getImage($image, $folder)
    {
        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path = $image->move(public_path('resources/'.$folder), $fileNameToStore);

        return $fileNameToStore;
        // $validatedData['image'] = $fileNameToStore;
    }

    protected function storeImage($image, $folder, $table, $tableId)
    {
        $validator = Validator::make(['image' => $image], [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ])->validate();

        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path = $image->move(public_path('resources/'.$folder), $fileNameToStore);
        $store = ['table_name' => $table, 'table_id' => $tableId, 'image' => $fileNameToStore];
        $getId = $this->imageRepository->store($store);

        return $getId;
    }

    protected function storeFile($image, $folder, $table, $tableId, $title)
    {
        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $path = $image->move(public_path('resources/'.$folder), $fileNameToStore);
        $store = ['table_name' => $table, 'table_id' => $tableId, 'image' => $fileNameToStore, 'file_title' => $title, 'file_type' => '1'];
        $getId = $this->imageRepository->store($store);

        return $getId;
    }
}
