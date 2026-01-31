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
            $table->bigInteger('amount')->unsigned(); // مبلغ به ریال
            $table->string('gateway', 50)->default('unknown');
            $table->string('authority')->nullable();
            $table->string('ref_id')->nullable();
            $table->tinyInteger('status')->default(0); // 0=pending, 1=success, 2=failed, 3=cancelled
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            // ایندکس‌ها
            $table->index(['user_id', 'center_id']);
            $table->index('authority');
            $table->index('ref_id');
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
