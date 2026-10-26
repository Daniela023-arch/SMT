<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos_suscripcion', function(Blueprint $t){$t->id();$t->foreignId('suscripcion_id')->constrained('suscripciones_tenant')->restrictOnDelete();$t->decimal('monto',12,2);$t->string('moneda',3)->default('MXN');$t->string('estado',25)->default('pendiente');$t->string('referencia_externa')->nullable()->unique();$t->timestamp('pagado_at')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_suscripcion');
    }
};
