<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorias', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->foreignId('categoria_padre_id')->nullable()->constrained('categorias')->nullOnDelete();$t->string('nombre');$t->timestamps();$t->unique(['tenant_id','nombre']);$t->unique(['id','tenant_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
