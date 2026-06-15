<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('organization_id', $request->user()->organization_id)
            ->where('is_active', true)
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'color' => 'nullable|string|max:20',
        ]);

        $category = Category::create([
            'organization_id' => $request->user()->organization_id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'color' => $request->color ?? '#3B82F6',
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        if ($category->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $category->update($request->only(['name', 'color', 'icon', 'sort_order', 'is_active', 'parent_id']));

        return response()->json($category);
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->organization_id !== $request->user()->organization_id) {
            abort(403);
        }

        $category->delete();
        return response()->json(['message' => 'Kategoriya o\'chirildi.']);
    }
}
