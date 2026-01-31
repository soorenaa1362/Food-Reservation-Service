<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // کد ملی هش‌شده (SHA-256 با salt اختیاری، اما حداقل منحصر به فرد باشه)
            $table->string('national_code_hashed', 64)->unique()->index();
            
            // شماره موبایل هش‌شده (برای تطبیق موقع ارسال OTP)
            $table->string('mobile_hashed', 64)->unique()->index();
            
            // نام و نام خانوادگی رمزنگاری‌شده (برای نمایش به کاربر)
            $table->text('encrypted_first_name')->nullable();
            $table->text('encrypted_last_name')->nullable();
            $table->text('encrypted_full_name')->nullable(); // اختیاری، برای جستجوی سریع‌تر
            
            // فیلدهای OTP
            $table->string('otp_code', 6)->nullable()->index();
            $table->timestamp('otp_expires_at')->nullable()->index();
            $table->unsignedTinyInteger('otp_attempts')->default(0); // جلوگیری از brute force
            $table->timestamp('otp_locked_until')->nullable(); // قفل موقت در صورت تلاش زیاد
            
            // وضعیت کاربر
            $table->boolean('is_active')->default(true)->index();
            
            $table->timestamps();
        });

        // چون از پسورد استفاده نمی‌کنیم، این جدول لازم نیست
        // Schema::create('password_reset_tokens', ...); → حذف شد

        // جدول sessions رو نگه می‌داریم چون Laravel Sanctum یا session-based auth ممکنه نیاز داشته باشه
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};