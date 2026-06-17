<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

class CompanyCategory extends Model
{
    protected $fillable = ['name', 'sort_order', 'is_active'];
}
