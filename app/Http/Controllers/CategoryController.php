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
        $this->authorize('access', Category::class);
        $category = $this->categoryRepository->all();

        return view('category', [
            'category' => $category,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Category::class);
        return view('addCategory');
    }

    public function store(CategoryRequest $request)
    {
        $this->authorize('create', Category::class);
        $validatedData = $request->validated();
        $this->categoryRepository->store($validatedData);

        return redirect()->route('category.add')->with('success', 'Record Inserted Successfully');
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Category::class);
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
