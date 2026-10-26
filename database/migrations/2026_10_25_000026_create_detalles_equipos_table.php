<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalles_equipos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('unidad_inventario_id')->unique()->constrained('unidades_inventario')->cascadeOnDelete();$t->string('marca')->nullable();$t->string('modelo')->nullable();$t->string('procesador')->nullable();$t->string('ram')->nullable();$t->string('almacenamiento')->nullable();$t->date('fecha_compra')->nullable();$t->date('fin_garantia')->nullable();$t->json('especificaciones')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_equipos');
    }
};
