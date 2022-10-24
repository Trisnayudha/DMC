<?php

namespace App\Models\Profiles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileModel extends Model
{
    use HasFactory;
    protected $table = 'profiles';
    protected $fillable = [
        'phone',
        'image',
        'job_title',
        'company_id',
        'users_id'
    ];
}
