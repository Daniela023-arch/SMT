<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asignaciones_promocion', function(Blueprint $t){$t->id();$t->foreignId('suscripcion_id')->constrained('suscripciones_tenant')->restrictOnDelete();$t->foreignId('promocion_id')->constrained('promociones_suscripcion')->restrictOnDelete();$t->timestamp('aplicada_at')->nullable();$t->timestamps();$t->unique(['suscripcion_id','promocion_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_promocion');
    }
};
