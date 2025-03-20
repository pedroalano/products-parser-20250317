<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $collection = 'cron_logs';

    protected $fillable = [
        'imported_at',
        'status',
        'details'
    ];
}
