<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('center_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true); // اگر HIS کاربر رو از مرکز حذف کرد
            $table->timestamps();

            // جلوگیری از تکرار
            $table->unique(['user_id', 'center_id']);
        });
    }

   
    public function down(): void
    {
        Schema::dropIfExists('center_user');
    }
};
