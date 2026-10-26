<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotes', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('producto_id')->constrained('productos')->restrictOnDelete();$t->string('numero_lote');$t->date('fecha_fabricacion')->nullable();$t->date('fecha_caducidad')->nullable();$t->timestamps();$t->unique(['tenant_id','producto_id','numero_lote']);$t->unique(['id','tenant_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
