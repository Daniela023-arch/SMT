<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proveedores', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();$t->string('nombre');$t->string('rfc',13)->nullable();$t->string('email')->nullable();$t->string('telefono',30)->nullable();$t->text('direccion')->nullable();$t->timestamps();$t->softDeletes();$t->unique(['id','tenant_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
