<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('idempotencias', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();$t->string('clave')->unique();$t->string('operacion',80);$t->string('estado',25)->default('procesando');$t->json('respuesta')->nullable();$t->timestamp('expira_at')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotencias');
    }
};
