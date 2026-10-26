<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movimientos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->foreignId('lote_id')->nullable()->constrained('lotes')->restrictOnDelete();$t->foreignId('unidad_inventario_id')->nullable()->constrained('unidades_inventario')->restrictOnDelete();$t->foreignId('ubicacion_origen_id')->nullable()->constrained('ubicaciones')->restrictOnDelete();$t->foreignId('ubicacion_destino_id')->nullable()->constrained('ubicaciones')->restrictOnDelete();$t->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();$t->string('tipo',25);$t->decimal('cantidad',14,3);$t->string('referencia_tipo',50)->nullable();$t->unsignedBigInteger('referencia_id')->nullable();$t->text('motivo')->nullable();$t->timestamps();$t->index(['tenant_id','producto_id','created_at']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movimientos');
    }
};
