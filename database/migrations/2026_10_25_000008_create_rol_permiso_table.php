<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rol_permiso', function(Blueprint $t){$t->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();$t->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete();$t->primary(['rol_id','permiso_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
    }
};
