<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes, Notifiable;

    protected $fillable = [
        'nom',
        'prenoms',
        'phone',
        'email',
        'lieu_habitation',
        'password',
        'solde'
    ];
    protected $hidden = ['password'];
    
    public function role(){
        return $this->belongsToMany(Role::class,'user_roles','user_id','role_id')->wherePivot('deleted_at',null);
    }

    public function package(){
        return $this->hasMany(Package::class, 'user_id');
    }

    public function souscription(){
        return $this->belongsToMany(Package::class,'achats','user_id','package_id')->withPivot('id');
    }
}
