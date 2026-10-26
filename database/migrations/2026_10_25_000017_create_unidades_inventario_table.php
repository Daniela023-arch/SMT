<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unidades_inventario', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();$t->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();$t->string('numero_serie')->nullable();$t->string('codigo_activo')->nullable();$t->string('estado',25)->default('disponible');$t->timestamps();$t->unique(['tenant_id','numero_serie']);$t->unique(['tenant_id','codigo_activo']);$t->unique(['id','tenant_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_inventario');
    }
};
