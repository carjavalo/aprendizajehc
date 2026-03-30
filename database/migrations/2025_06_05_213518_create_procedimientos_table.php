<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('procedimientos', function (Blueprint $table) {
            $table->id();
            $table->integer('Cod_Episodio')->nullable();
            $table->integer('Cod_Sala')->nullable();
            $table->string('Nom_Sala', 100)->nullable();
            $table->string('Num_Cama', 20)->nullable();
            $table->dateTime('F_Ingreso')->nullable();
            $table->string('Cod_Eps', 20)->nullable();
            $table->string('Nom_Eps', 100)->nullable();
            $table->integer('Hist_Clinica')->nullable();
            $table->string('Tipo_Ident', 5)->nullable();
            $table->string('Num_Ident', 20)->nullable();
            $table->integer('Edad')->nullable();
            $table->char('Sexo', 1)->nullable();
            $table->string('Servicio', 100)->nullable();
            $table->string('Estado', 50)->nullable();
            $table->string('Medico_Trata', 100)->nullable();
            $table->string('Cod_Diag', 10)->nullable();
            $table->string('CIE10', 10)->nullable();
            $table->string('Diagnostico', 255)->nullable();
            $table->string('Antimicrobiano', 100)->nullable();
            $table->string('Cantidad', 50)->nullable();
            $table->string('Presentacion', 50)->nullable();
            $table->string('Via_Aplicacion', 50)->nullable();
            $table->string('Tiem_Horas', 50)->nullable();
            $table->string('Dias_Antibioticos', 50)->nullable();
            $table->date('Fec_Sumistro')->nullable();
            $table->time('Ho_Sumisnistro')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedimientos');
    }
};
