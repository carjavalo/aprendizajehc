<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VinculacionContrato extends Model
{
    protected $table = 'vinculacion_contrato';

    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class, 'vinculacion_contrato_id');
    }
}
