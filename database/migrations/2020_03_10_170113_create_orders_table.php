<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('meal');
            $table->decimal('price');
            $table->decimal('discount_price');
            $table->date('date');

            $table->unsignedInteger('meal_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('restaurant_id');
            $table->unsignedInteger('company_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
