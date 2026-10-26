<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ubicaciones', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('ubicacion_padre_id')->nullable()->constrained('ubicaciones')->nullOnDelete();$t->string('nombre');$t->string('codigo')->nullable();$t->timestamps();$t->unique(['id','tenant_id']);$t->unique(['tenant_id','codigo']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
