<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prestamos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();$t->string('folio');$t->string('prestatario_nombre');$t->string('prestatario_contacto')->nullable();$t->date('fecha_prestamo');$t->date('fecha_limite');$t->string('estado',25)->default('activo');$t->timestamps();$t->unique(['tenant_id','folio']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
