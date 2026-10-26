<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bajas', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();$t->string('folio');$t->string('motivo',60);$t->string('estado',25)->default('borrador');$t->timestamp('aprobada_at')->nullable();$t->timestamps();$t->unique(['tenant_id','folio']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('bajas');
    }
};
