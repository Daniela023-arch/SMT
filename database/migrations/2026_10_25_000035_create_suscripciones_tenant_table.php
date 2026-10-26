<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suscripciones_tenant', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('plan_id')->constrained('planes_suscripcion')->restrictOnDelete();$t->string('estado',25)->default('prueba');$t->string('proveedor')->nullable();$t->string('suscripcion_externa_id')->nullable();$t->timestamp('inicio_at');$t->timestamp('fin_at')->nullable();$t->timestamp('cancelada_at')->nullable();$t->timestamps();$t->unique(['proveedor','suscripcion_externa_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones_tenant');
    }
};
