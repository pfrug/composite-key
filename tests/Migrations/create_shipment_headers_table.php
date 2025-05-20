<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_headers', function (Blueprint $table) {
            $table->string('company_code', 10);
            $table->string('shipment_number', 20);
            $table->string('description', 100)->nullable();
            $table->primary(['company_code', 'shipment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_headers');
    }
};
