<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modulos_tenant', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('modulo_id')->constrained('modulos')->restrictOnDelete();$t->boolean('habilitado')->default(true);$t->timestamps();$t->unique(['tenant_id','modulo_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos_tenant');
    }
};
