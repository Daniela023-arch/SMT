<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('existencias', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->foreignId('ubicacion_id')->constrained('ubicaciones')->restrictOnDelete();$t->foreignId('lote_id')->nullable()->constrained('lotes')->restrictOnDelete();$t->decimal('cantidad',14,3)->default(0);$t->timestamps();$t->index(['tenant_id','producto_id','ubicacion_id','lote_id'],'existencias_busqueda');});
    }

    public function down(): void
    {
        Schema::dropIfExists('existencias');
    }
};
