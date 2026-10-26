<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('importaciones_csv', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();$t->string('archivo');$t->string('estado',25)->default('pendiente');$t->unsignedInteger('filas_totales')->default(0);$t->unsignedInteger('filas_exitosas')->default(0);$t->unsignedInteger('filas_fallidas')->default(0);$t->json('errores')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_csv');
    }
};
