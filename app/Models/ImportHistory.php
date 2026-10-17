<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $fillable = [
        'report_type', 'original_filename', 'file_size', 'status',
        'error_message', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];
}
