<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalles_venta', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('venta_id')->constrained('ventas')->restrictOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->foreignId('unidad_inventario_id')->nullable()->constrained('unidades_inventario')->restrictOnDelete();$t->foreignId('lote_id')->nullable()->constrained('lotes')->restrictOnDelete();$t->decimal('cantidad',14,3);$t->decimal('precio_unitario',14,2);$t->decimal('descuento',14,2)->default(0);$t->decimal('impuesto',14,2)->default(0);$t->decimal('total',14,2);$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_venta');
    }
};
