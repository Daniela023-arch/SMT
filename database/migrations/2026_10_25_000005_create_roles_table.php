<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete();$t->string('nombre');$t->string('slug');$t->boolean('es_sistema')->default(false);$t->timestamps();$t->unique(['tenant_id','slug']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
