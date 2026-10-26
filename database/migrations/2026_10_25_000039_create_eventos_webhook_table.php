<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos_webhook', function(Blueprint $t){$t->id();$t->string('proveedor',50);$t->string('evento_externo_id');$t->string('tipo',80);$t->json('payload');$t->string('estado',25)->default('recibido');$t->unsignedInteger('intentos')->default(0);$t->timestamp('procesado_at')->nullable();$t->text('ultimo_error')->nullable();$t->timestamps();$t->unique(['proveedor','evento_externo_id']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_webhook');
    }
};
