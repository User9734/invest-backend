<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'role_id'
    ];
}
