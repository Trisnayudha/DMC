<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationModel extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $casts = ['id' => 'int'];
    protected $fillable = [
        'type',
        'message',
        'category',
        'title',
        'target_slug',
        'target_id',
        'uname',
        'is_usname_connected',
        'users_id',
        'all_users'
    ];
}
