<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = [
        'cout',
        'produits_vendus'
    ];
    use SoftDeletes;
    public function achat(){
        return $this->belongsTo(Achat::class);
    }
}
