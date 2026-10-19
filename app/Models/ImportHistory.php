<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $fillable = [
        'report_type',
        'report_key',
        'original_filename',
        'file_size',
        'status',
        'error_message',
        'completed_at',
        'stored_path',
        'total_rows',
        'processed_rows',
        'next_row',
    ];

    protected $casts = ['completed_at' => 'datetime'];
}
