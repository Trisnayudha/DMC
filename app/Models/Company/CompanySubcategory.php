<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanySubcategory extends Model
{
    protected $fillable = ['company_category_id', 'name', 'sort_order', 'is_active'];

    /**
     * Parent category. Subcategory selalu milik satu kategori (hierarki).
     */
    public function category()
    {
        return $this->belongsTo(CompanyCategory::class, 'company_category_id');
    }

    /**
     * Company yang memilih subcategory ini (many-to-many via pivot).
     */
    public function companies()
    {
        return $this->belongsToMany(
            CompanyModel::class,
            'company_subcategory_company',
            'company_subcategory_id',
            'company_id'
        );
    }
}
