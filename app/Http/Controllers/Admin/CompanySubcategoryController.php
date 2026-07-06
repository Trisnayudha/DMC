<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyCategory;
use App\Models\Company\CompanySubcategory;
use Illuminate\Http\Request;

class CompanySubcategoryController extends Controller
{
    public function index()
    {
        $categories = CompanyCategory::with('subcategories')
            ->orderBy('sort_order')
            ->get();

        return view('admin.company_subcategory.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_category_id' => 'required|integer|exists:company_categories,id',
            'name'                => 'required|string|max:255',
        ]);

        $exists = CompanySubcategory::where('company_category_id', $request->company_category_id)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'Subcategory "' . $request->name . '" sudah ada di kategori tersebut.');
        }

        $maxSort = CompanySubcategory::where('company_category_id', $request->company_category_id)
            ->max('sort_order') ?? 0;

        CompanySubcategory::create([
            'company_category_id' => $request->company_category_id,
            'name'                => $request->name,
            'sort_order'          => $maxSort + 1,
            'is_active'           => true,
        ]);

        return back()->with('success', 'Subcategory "' . $request->name . '" berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $subcategory = CompanySubcategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $exists = CompanySubcategory::where('company_category_id', $subcategory->company_category_id)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Subcategory "' . $request->name . '" sudah ada di kategori tersebut.');
        }

        $subcategory->update(['name' => $request->name]);

        return back()->with('success', 'Subcategory berhasil diupdate.');
    }

    public function toggleActive($id)
    {
        $subcategory = CompanySubcategory::findOrFail($id);
        $subcategory->is_active = !$subcategory->is_active;
        $subcategory->save();

        $status = $subcategory->is_active ? 'activated' : 'deactivated';
        return back()->with('success', '"' . $subcategory->name . '" berhasil di-' . $status . '.');
    }
}
