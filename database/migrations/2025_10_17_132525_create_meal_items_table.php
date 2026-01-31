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
        Schema::create('meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained()->cascadeOnDelete();
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->string('food_name');
            $table->unsignedBigInteger('price')->default(0);         // بهتر از unsignedInteger اگر مبلغ زیاد بشه
            $table->unsignedMediumInteger('portions')->default(0);   // معمولاً ۰ تا ۶۵۵۳۵ کافیه
            $table->unsignedMediumInteger('reserved_count')->default(0);
            $table->timestamps();

            // جلوگیری از تکرار یک غذا در یک وعده
            $table->unique(['meal_id', 'meal_type', 'food_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
