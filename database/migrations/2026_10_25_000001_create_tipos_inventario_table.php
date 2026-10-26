<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipos_inventario', function(Blueprint $t){$t->id();$t->string('nombre')->unique();$t->text('descripcion')->nullable();$t->timestamps();});
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_inventario');
    }
};
