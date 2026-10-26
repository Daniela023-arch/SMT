<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pagos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('venta_id')->constrained('ventas')->restrictOnDelete();$t->decimal('monto',14,2);$t->string('metodo',30);$t->string('referencia')->nullable();$t->string('estado',25)->default('pendiente');$t->timestamp('pagado_at')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
