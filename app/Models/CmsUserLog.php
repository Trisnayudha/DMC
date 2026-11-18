<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsUserLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'record_ids' => 'array',
        'fields' => 'array',
        'occurred_at' => 'datetime',
        'last_activity' => 'datetime',
    ];

    public const ACTIONS = [
        'menu' => 'Open menu',
        'view' => 'View page or details',
        'load' => 'Load data',
        'open_create' => 'Open create form',
        'open_edit' => 'Open edit form',
        'create' => 'Create record',
        'update' => 'Update record',
        'delete' => 'Delete record',
        'export' => 'Export or download',
        'import' => 'Import data',
        'action' => 'Run action',
        'login' => 'Log in',
        'logout' => 'Log out',
        'click' => 'Click control',
        'field' => 'Change form field',
    ];

    public function outcomeLabel(): string
    {
        if (!$this->method) {
            return 'Browser interaction';
        }

        return [
            'denied' => 'Access denied',
            'invalid' => 'Validation failed',
            'failed' => 'Failed',
            'redirect' => 'Redirected',
            'completed' => 'Request completed',
        ][$this->outcome] ?? 'Outcome unavailable';
    }
}
