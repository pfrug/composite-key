<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipment_details', function (Blueprint $table) {
            $table->string('company_code');
            $table->string('shipment_number');
            $table->integer('line_number');
            $table->integer('detail_sequence');
            $table->string('detail_description');
            $table->decimal('weight', 8, 2)->nullable();

            $table->primary(['company_code', 'shipment_number', 'line_number', 'detail_sequence']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipment_details');
    }
};