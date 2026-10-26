<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();$t->string('sku');$t->string('nombre');$t->text('descripcion')->nullable();$t->string('unidad_medida',30)->default('pieza');$t->decimal('precio_venta',12,2)->nullable();$t->decimal('stock_minimo',14,3)->default(0);$t->boolean('requiere_serie')->default(false);$t->boolean('activo')->default(true);$t->timestamps();$t->softDeletes();$t->unique(['tenant_id','sku']);$t->unique(['id','tenant_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
