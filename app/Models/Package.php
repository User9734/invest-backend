<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'cout_acquisition',
        'nb_products',
        'cout_vente',
        'nb_jours',
        'user_id',
        'type_id',
        'libelle'
    ];
    use SoftDeletes;

    public function user(){
        return $this->belongsToMany(User::class,'achats','package_id','user_id')->withPivot('id')->wherePivot('consommed',0)->wherePivot('deleted_at',null);
    }

    public function type(){
        return $this->belongsTo(Type::class);
    }

    public function seller(){
        return $this->belongsTo(User::class);
    }

    public function sell(){
        return $this->hasMany(Achat::class, 'package_id');
    }
}
