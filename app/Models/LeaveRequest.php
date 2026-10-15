<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'leave_start_date' => 'date',
        'leave_end_date' => 'date',
        'raised_on' => 'datetime',
    ];
}