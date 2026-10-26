<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas', function(Blueprint $t){$t->id();$t->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();$t->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();$t->string('folio');$t->string('estado',25)->default('borrador');$t->decimal('subtotal',14,2)->default(0);$t->decimal('impuestos',14,2)->default(0);$t->decimal('descuento',14,2)->default(0);$t->decimal('total',14,2)->default(0);$t->timestamps();$t->unique(['tenant_id','folio']);});
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
