<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procedimiento extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'procedimientos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Cod_Episodio',
        'Cod_Sala',
        'Nom_Sala',
        'Num_Cama',
        'F_Ingreso',
        'Cod_Eps',
        'Nom_Eps',
        'Hist_Clinica',
        'Tipo_Ident',
        'Num_Ident',
        'Edad',
        'Sexo',
        'Servicio',
        'Estado',
        'Medico_Trata',
        'Cod_Diag',
        'CIE10',
        'Diagnostico',
        'Antimicrobiano',
        'Cantidad',
        'Presentacion',
        'Via_Aplicacion',
        'Tiem_Horas',
        'Dias_Antibioticos',
        'Fec_Sumistro',
        'Ho_Sumisnistro',
    ];

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'F_Ingreso' => 'datetime',
        'Fec_Sumistro' => 'date',
        'Ho_Sumisnistro' => 'time',
        'Edad' => 'integer',
        'Cod_Episodio' => 'integer',
        'Cod_Sala' => 'integer',
        'Hist_Clinica' => 'integer',
    ];
}
