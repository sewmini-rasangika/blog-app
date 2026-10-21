<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('posts')->latest()->paginate(10);
        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tags.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:50|unique:tags,name',
        ]);
        $data['slug'] = Str::slug($data['name']);

        $tag = Tag::create($data);
        Activity::log('tag.created', "Created tag {$tag->name}", $tag);

        return redirect()->route('tags.index')->with('success', 'Tag created.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => ['required', 'max:50', Rule::unique('tags', 'name')->ignore($tag->id)],
        ]);
        $data['slug'] = Str::slug($data['name']);

        $tag->update($data);
        Activity::log('tag.updated', "Updated tag {$tag->name}", $tag);

        return redirect()->route('tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        Activity::log('tag.deleted', "Deleted tag {$tag->name}");
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag deleted.');
    }
}
