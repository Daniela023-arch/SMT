<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permisos', function(Blueprint $t){$t->id();$t->string('clave')->unique();$t->string('descripcion')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
