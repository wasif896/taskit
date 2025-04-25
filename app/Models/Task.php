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
        'due_date',
        'start_time',
        'end_time',
        'is_high_priority',
        'status',
        'user_id',
          'url',
          'picture',
          'video'
    ];

    protected $hidden = [
        'updated_at',
        // 'created_at',
        'user_id',
    ];

    public function toArray()
    {
        $array = parent::toArray();
        $array['picture'] = $array['picture']!=null ? url($array['picture']) : "";
        $array['video'] = $array['video']!=null ? url($array['video']) : "";
        $array['create_date'] = strval($this->created_at ? $this->created_at->timestamp : null);
        $array['due_date'] = strval($array['due_date']);
        $array['is_high_priority'] = intval($array['is_high_priority']);
        unset($array['created_at']);
        return $array;
    }
}
