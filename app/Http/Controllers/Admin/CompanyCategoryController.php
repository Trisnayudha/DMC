<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyCategory;
use Illuminate\Http\Request;

class CompanyCategoryController extends Controller
{
    public function index()
    {
        $categories = CompanyCategory::orderBy('sort_order')->get();
        return view('admin.company_category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:company_categories,name',
        ]);

        $maxSort = CompanyCategory::max('sort_order') ?? 0;

        CompanyCategory::create([
            'name'       => $request->name,
            'sort_order' => $maxSort + 1,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Category "' . $request->name . '" berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $category = CompanyCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:company_categories,name,' . $id,
        ]);

        $category->update(['name' => $request->name]);

        return back()->with('success', 'Category berhasil diupdate.');
    }

    public function toggleActive($id)
    {
        $category = CompanyCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'activated' : 'deactivated';
        return back()->with('success', '"' . $category->name . '" berhasil di-' . $status . '.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:company_categories,id',
        ]);

        foreach ($request->ids as $i => $id) {
            CompanyCategory::where('id', $id)->update(['sort_order' => $i + 1]);
        }

        return response()->json(['success' => true]);
    }
}
