<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('producto_proveedor', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();$t->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();$t->string('codigo_proveedor')->nullable();$t->decimal('costo_referencia',12,2)->nullable();$t->timestamps();$t->unique(['tenant_id','producto_id','proveedor_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_proveedor');
    }
};
