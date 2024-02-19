<?php

namespace App\Http\Controllers;

use App\Repositories\ImageRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getImage($image, $folder) {
        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
        $path = $image->move(public_path('resources/' . $folder), $fileNameToStore);
        return $fileNameToStore;
        // $validatedData['image'] = $fileNameToStore;
    }

    protected function storeImage($image, $folder, $table, $tableId) {
        $validator = Validator::make(['image' => $image], [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ])->validate();
        
        $fileNameWithExtension = $image->getClientOriginalName();
        $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);
        $extension = $image->getClientOriginalExtension();
        $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
        $path = $image->move(public_path('resources/' . $folder), $fileNameToStore);
        $store = ['table_name' => $table, 'table_id' => $tableId, 'image' => $fileNameToStore];
        $getId = $this->imageRepository->store($store);
        return $getId;
    }
}
