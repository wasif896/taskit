<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'date',
        'starttime',
        'endtime',
        'is_high_priority',
        'status',
        'user_id',
    ];
}
