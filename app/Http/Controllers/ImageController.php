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

    public function destroy(Image $id, $dir)
    {
        $this->imageRepository->delete($id->image_id);
        unlink('resources/'.$dir.'/'.$id->image);
        if (strpos($dir, 'file')) {
            return back()->with('success', 'File Deleted Successfully');
        } else {
            return back()->with('success', 'Image Deleted Successfully');
        }
    }
}
