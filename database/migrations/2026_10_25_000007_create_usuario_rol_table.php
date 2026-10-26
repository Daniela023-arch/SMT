<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuario_rol', function(Blueprint $t){$t->id();$t->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();$t->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();$t->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();$t->unique(['usuario_id','rol_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
