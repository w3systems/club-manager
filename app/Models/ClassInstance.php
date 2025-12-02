<?php

namespace App\Models;

use App\Core\Model;

class ClassInstance extends Model
{
    protected static string $table = 'class_instances';

    protected static array $fillable = [
        'class_parent_id',
        'instance_date_time',
    ];

    protected static array $casts = [
        'class_parent_id' => 'integer',
        'instance_date_time' => 'datetime',
    ];
}