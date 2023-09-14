<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitModel extends Model
{
    use HasFactory;
    protected $table = 'visit_booth';

    protected $fillable = [
        'name', 'company_name', 'job_title', 'phone', 'email'
    ];
}
