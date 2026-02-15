<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRateLimit extends Model
{
    protected $fillable = [
        'service',
        'endpoint',
        'calls_in_current_second',
        'calls_in_current_minute',
        'calls_today',
        'second_window_start',
        'minute_window_start',
        'day_window_start',
    ];

    protected $casts = [
        'second_window_start' => 'datetime',
        'minute_window_start' => 'datetime',
        'day_window_start' => 'datetime',
    ];
}
