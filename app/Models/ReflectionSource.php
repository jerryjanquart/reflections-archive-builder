<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReflectionSource extends Model
{
    protected $fillable = [
        'title',
        'url',
        'post_date',
        'status',
        'files_created',
        'error_message',
        'processed_at',
    ];
}
