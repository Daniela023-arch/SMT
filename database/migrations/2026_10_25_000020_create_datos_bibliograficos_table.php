<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('datos_bibliograficos', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();$t->string('isbn')->nullable();$t->string('autor')->nullable();$t->string('editorial')->nullable();$t->unsignedSmallInteger('anio_publicacion')->nullable();$t->string('edicion')->nullable();$t->string('idioma',30)->nullable();$t->timestamps();$t->unique(['tenant_id','producto_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_bibliograficos');
    }
};
