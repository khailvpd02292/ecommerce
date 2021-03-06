<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('image')->nullable();
            $table->text('description')->nullable();
            $table->string('api_id')->nullable();
            $table->bigInteger('price')->nullable();
            $table->bigInteger('pre_tax_price')->nullable();
            $table->bigInteger('quantity')->default(0);
            $table->tinyInteger('product_status_id')->default(1); // 1 draft, 2 public
            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
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
        Schema::dropIfExists('products');
    }
}
