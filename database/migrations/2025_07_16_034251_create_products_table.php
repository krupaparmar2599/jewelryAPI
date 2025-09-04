<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('stock_quantity')->default(0);
        $table->string('material')->nullable(); // gold, silver, etc.
        $table->decimal('weight', 8, 2)->nullable(); // in grams
        $table->string('type')->nullable(); // ring, earring, etc.
        $table->string('image')->nullable(); // will store image URL or path
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
