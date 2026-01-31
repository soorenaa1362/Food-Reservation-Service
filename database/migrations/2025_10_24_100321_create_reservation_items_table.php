<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('meal_item_id')->nullable()->constrained()->onDelete('set null'); // اختیاری: لینک به MealItem
            $table->string('food_name');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->integer('quantity');
            $table->decimal('price', 12, 0); // قیمت واحد به ریال
            $table->decimal('total', 12, 0); // مبلغ کل آیتم
            $table->date('date'); // تاریخ وعده
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservation_items');
    }
};
