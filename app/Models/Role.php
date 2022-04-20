<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'libelle'
    ];
    use SoftDeletes;

    public function role(){
        return $this->belongsToMany(Role::class,'user_roles','user_id','role_id');
    }
}
