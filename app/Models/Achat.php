<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    protected $fillable = ['package_id'];
    protected $attributes = ['validation' => 0, ];
    use SoftDeletes;

    public function package(){
        return $this->belongsTo(Package::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function rapport(){
        return $this->hasMany(Rapport::class, 'achat_id');
    }
}
