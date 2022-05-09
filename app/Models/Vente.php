<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable = ['nb_ventes', 'package_id'];
    use SoftDeletes;

    public function package(){
        return $this->belongsTo(Package::class);
    }

    public function seller()
    {
        return $this->hasOneThrough(User::class, Package::class);
    }
}
