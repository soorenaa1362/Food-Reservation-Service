<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('center_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 0)->default(0); // اعتبار فعلی (به ریال یا تومان)
            // $table->decimal('initial_credit', 12, 0)->default(0); // اعتبار اولیه از HIS
            $table->string('membership_type')->nullable(); // نوع عضویت: رسمی، قراردادی، پاره‌وقت و ...
            $table->date('credit_expires_at')->nullable(); // تاریخ انقضای اعتبار (اگر داشته باشه)
            $table->timestamps();

            // جلوگیری از تکرار: هر کاربر در هر مرکز فقط یک اعتبار
            $table->unique(['user_id', 'center_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_cards');
    }
};
