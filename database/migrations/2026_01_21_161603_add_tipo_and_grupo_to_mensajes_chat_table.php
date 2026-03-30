<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensajes_chat', function (Blueprint $table) {
            if (!Schema::hasColumn('mensajes_chat', 'tipo')) {
                $table->enum('tipo', ['individual', 'grupal'])->default('individual')->after('mensaje');
            }
            if (!Schema::hasColumn('mensajes_chat', 'grupo_destinatario')) {
                $table->string('grupo_destinatario')->nullable()->after('tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mensajes_chat', function (Blueprint $table) {
            if (Schema::hasColumn('mensajes_chat', 'tipo')) {
                $table->dropColumn('tipo');
            }
            if (Schema::hasColumn('mensajes_chat', 'grupo_destinatario')) {
                $table->dropColumn('grupo_destinatario');
            }
        });
    }
};
