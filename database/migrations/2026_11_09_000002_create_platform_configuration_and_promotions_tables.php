<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones_plataforma', function (Blueprint $table): void {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('promociones_publicas', function (Blueprint $table): void {
            $table->id();
            $table->string('titulo', 160);
            $table->string('etiqueta', 40)->nullable();
            $table->text('descripcion');
            $table->string('texto_boton', 60)->nullable();
            $table->string('url_boton', 255)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('vigencia_inicio')->nullable();
            $table->timestamp('vigencia_fin')->nullable();
            $table->foreignId('actualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones_publicas');
        Schema::dropIfExists('configuraciones_plataforma');
    }
};
