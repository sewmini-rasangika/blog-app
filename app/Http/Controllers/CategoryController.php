<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:100|unique:categories,name',
        ]);
        $data['slug'] = Str::slug($data['name']);

        $category = Category::create($data);
        Activity::log('category.created', "Created category {$category->name}", $category);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);
        $data['slug'] = Str::slug($data['name']);

        $category->update($data);
        Activity::log('category.updated', "Updated category {$category->name}", $category);

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
       
        if ($category->posts()->withTrashed()->exists()) {
            return back()->with('error', 'Cannot deleted a category that has a posts.');
        }

        Activity::log('category.deleted', "Deleted category {$category->name}");
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
