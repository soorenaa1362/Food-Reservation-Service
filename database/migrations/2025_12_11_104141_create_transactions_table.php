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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('center_id')->constrained()->onDelete('cascade');

            $table->tinyInteger('type')->nullable(); // افزایش اعتبار یا رزرو غذا
            $table->bigInteger('amount');           // مبلغ تراکنش
            $table->tinyInteger('status')->default(0); // pending / success / failed / cancelled
            $table->text('description')->nullable();  // توضیحات دلخواه
            $table->json('meta')->nullable();        // داده اضافی

            $table->timestamps();

            // ایندکس‌ها
            $table->index(['user_id', 'center_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
