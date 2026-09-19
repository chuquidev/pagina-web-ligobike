<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Código del producto en tu sistema de inventario (POS). Nulo = producto
            // creado manualmente en el panel, sin seguimiento de stock por Excel.
            $table->string('sku')->nullable()->unique()->after('id');

            // Stock real. Null = no se controla inventario para este producto (comportamiento
            // anterior, disponibilidad manual). 0 = agotado, se oculta del catálogo público.
            $table->integer('stock')->nullable()->after('sale_price');

            // Precio mínimo de referencia (uso interno del negocio, no se expone al público).
            $table->decimal('min_price', 10, 2)->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sku', 'stock', 'min_price']);
        });
    }
};
