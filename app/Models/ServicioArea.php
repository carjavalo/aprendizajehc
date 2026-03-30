<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicioArea extends Model
{
    protected $table = 'servicios_areas';

    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class, 'servicio_area_id');
    }
}
