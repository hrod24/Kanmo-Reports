<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAttendance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'attendance_date' => 'date',
    ];
}
