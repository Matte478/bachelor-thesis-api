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
//            $table->foreign('meal_id')
//                ->references('id')
//                ->on('meals')
//                ->onUpdate('cascade')
//                ->onDelete('cascade');

            $table->unsignedInteger('user_id');
//            $table->foreign('user_id')
//                ->references('id')
//                ->on('users')
//                ->onUpdate('cascade')
//                ->onDelete('cascade');

            $table->unsignedInteger('restaurant_id');
//            $table->foreign('restaurant_id')
//                ->references('id')
//                ->on('restaurants')
//                ->onUpdate('cascade')
//                ->onDelete('cascade');

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
