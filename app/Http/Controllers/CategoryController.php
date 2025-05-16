<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->middleware(['auth', 'all']);
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $category = $this->categoryRepository->all();

        return view('category', [
            'category' => $category,
        ]);
    }

    public function create()
    {
        return view('addCategory');
    }

    public function store(CategoryRequest $request)
    {
        $validatedData = $request->validated();
        $this->categoryRepository->store($validatedData);

        return redirect()->route('category.add')->with('success', 'Record Inserted Successfully');
    }

    public function update(Request $request, $id)
    {
        $this->categoryRepository->update($id, $request->input());

        return redirect()->route('category')->with('success', 'Record Updated Successfully');
    }

    public function edit(Category $category)
    {
    }

    public function show(Category $category)
    {
    }

    public function destroy(Category $category)
    {
    }
}
