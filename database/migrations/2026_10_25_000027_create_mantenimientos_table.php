<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mantenimientos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('unidad_inventario_id')->constrained('unidades_inventario')->restrictOnDelete();$t->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();$t->string('tipo',30);$t->string('estado',25)->default('programado');$t->text('descripcion')->nullable();$t->date('fecha_programada')->nullable();$t->timestamp('fecha_realizada')->nullable();$t->decimal('costo',12,2)->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
