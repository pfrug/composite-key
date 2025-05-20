<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('shipment_lines', function (Blueprint $table) {
            $table->string('company_code', 10);
            $table->string('shipment_number', 20);
            $table->unsignedInteger('line_number');
            $table->string('product_name', 100);
            $table->unsignedInteger('quantity');
            $table->primary(['company_code', 'shipment_number', 'line_number']);

            $table->foreign(['company_code', 'shipment_number'])
                  ->references(['company_code', 'shipment_number'])
                  ->on('shipment_headers')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_lines');
    }
};
