<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalles_prestamos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('prestamo_id')->constrained('prestamos')->restrictOnDelete();$t->foreignId('unidad_inventario_id')->constrained('unidades_inventario')->restrictOnDelete();$t->timestamp('devuelto_at')->nullable();$t->string('estado',25)->default('prestado');$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_prestamos');
    }
};
