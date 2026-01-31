<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centers', function (Blueprint $table) {
            $table->id();
            $table->string('his_center_id')->unique(); // آی‌دی مرکز در HIS (در JSON رشته است: "1", "2" ...)
            $table->string('name');
            $table->string('type'); // بیمارستان، درمانگاه شهری و ...
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // ایندکس برای جستجوی سریع
            $table->index('his_center_id');
        });
    }

  
    public function down(): void
    {
        Schema::dropIfExists('centers');
    }
};
