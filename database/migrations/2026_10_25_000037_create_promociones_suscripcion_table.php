<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promociones_suscripcion', function(Blueprint $t){$t->id();$t->string('codigo')->unique();$t->string('tipo',20);$t->decimal('valor',12,2);$t->timestamp('vigencia_inicio')->nullable();$t->timestamp('vigencia_fin')->nullable();$t->unsignedInteger('usos_maximos')->nullable();$t->boolean('activo')->default(true);$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('promociones_suscripcion');
    }
};
