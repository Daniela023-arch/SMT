<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalles_baja', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('baja_id')->constrained('bajas')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->foreignId('unidad_inventario_id')->nullable()->constrained('unidades_inventario')->restrictOnDelete();$t->foreignId('lote_id')->nullable()->constrained('lotes')->restrictOnDelete();$t->decimal('cantidad',14,3);$t->text('observacion')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_baja');
    }
};
