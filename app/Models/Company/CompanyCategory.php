<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyCategory extends Model
{
    protected $fillable = ['name', 'sort_order', 'is_active'];

    /**
     * Daftar subcategory milik kategori ini (hierarki).
     */
    public function subcategories()
    {
        return $this->hasMany(CompanySubcategory::class, 'company_category_id')
            ->orderBy('sort_order');
    }
}
