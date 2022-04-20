<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    protected $fillable = ['nb_ventes', 'cout_total', 'type_id'];
    use SoftDeletes;
}
