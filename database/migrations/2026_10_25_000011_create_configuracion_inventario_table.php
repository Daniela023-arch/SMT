<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracion_inventario', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();$t->boolean('permitir_stock_negativo')->default(false);$t->boolean('controlar_lotes')->default(false);$t->boolean('controlar_caducidad')->default(false);$t->boolean('controlar_series')->default(false);$t->json('opciones')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_inventario');
    }
};
