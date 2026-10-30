<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $table = 'contact_inquiry';

    const SOURCE_WEB = 'web';
    const SOURCE_APPS = 'apps';

    protected $fillable = [
        'source',
        'users_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'category',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
