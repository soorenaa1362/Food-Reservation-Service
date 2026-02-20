<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_meal_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner'])->index();
            
            $table->unsignedTinyInteger('reservation_to_hour')
                ->comment('ساعت پایان مجاز رزرو برای همان روز (0-23)');
            
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->unique(['center_id', 'meal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_meal_deadlines');
    }
};
