<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('actividades')) {
            Schema::create('actividades', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
