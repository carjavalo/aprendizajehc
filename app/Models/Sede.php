<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    protected $table = 'sedes';

    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class, 'sede_id');
    }
}
