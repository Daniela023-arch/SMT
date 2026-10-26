<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('planes_suscripcion', function(Blueprint $t){$t->id();$t->string('nombre')->unique();$t->decimal('precio',12,2);$t->string('moneda',3)->default('MXN');$t->string('periodicidad',20);$t->unsignedInteger('limite_usuarios')->nullable();$t->unsignedInteger('limite_productos')->nullable();$t->boolean('activo')->default(true);$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_suscripcion');
    }
};
