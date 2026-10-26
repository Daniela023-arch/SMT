<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function(Blueprint $t){$t->id();$t->string('nombre');$t->string('slug')->unique();$t->string('email')->nullable();$t->string('estado',30)->default('pendiente');$t->foreignId('tipo_inventario_id')->nullable()->constrained('tipos_inventario')->nullOnDelete();$t->timestamps();$t->softDeletes();});
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
