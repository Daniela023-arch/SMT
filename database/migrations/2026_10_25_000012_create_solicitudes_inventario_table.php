<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitudes_inventario', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();$t->foreignId('usuario_solicitante_id')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('tipo_inventario_id')->constrained('tipos_inventario');$t->string('nombre_negocio');$t->string('email_contacto');$t->string('estado',25)->default('pendiente');$t->text('observaciones')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_inventario');
    }
};
