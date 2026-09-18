<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create([
            ...$request->safe()->except('image'),
            'image' => $this->storeImage($request),
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category added.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            ...$request->safe()->except('image'),
            // Keep the existing picture when no replacement was supplied.
            'image' => $this->storeImage($request) ?? $category->image,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Products reference their category, so one still in use cannot go.
        // Refusing is safer than silently moving its products somewhere else.
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => "\"{$category->name}\" still has products and cannot be deleted.",
            ]);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category removed.');
    }

    private function storeImage(CategoryRequest $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = Str::uuid().'.'.$file->extension();

        $file->storeAs('', $name, 'categories');

        return 'images/categories/'.$name;
    }
}
