<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnDuty extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raised_on' => 'date',
        'raised_for' => 'date',
    ];
}
