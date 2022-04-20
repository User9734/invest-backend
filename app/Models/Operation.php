<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
    ];

    use SoftDeletes;
}
