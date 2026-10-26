<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('codigos_identificacion', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('producto_id')->nullable()->constrained('productos')->cascadeOnDelete();$t->foreignId('unidad_inventario_id')->nullable()->constrained('unidades_inventario')->cascadeOnDelete();$t->string('tipo',25);$t->string('codigo');$t->timestamps();$t->unique(['tenant_id','tipo','codigo']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_identificacion');
    }
};
