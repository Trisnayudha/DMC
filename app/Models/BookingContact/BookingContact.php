<?php

namespace App\Models\BookingContact;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingContact extends Model
{
    use HasFactory;
    protected $table = 'booking_contact';
    protected $fillable = [
        'name_contact',
        'email_contact',
        'phone_contact',
        'job_title_contact',
        'prefix',
        'company_name',
        'address',
        'city',
        'company_website',
        'country',
        'portal_code',
        'company_category',
        'company_other',
        'office_number',
        'status',
        'invoice'
    ];
}
