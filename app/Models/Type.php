<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = [
        'libelle'
    ];
    use SoftDeletes;
    public function package(){
        return $this->hasMany(Package::class, 'type_id');
    }
}
