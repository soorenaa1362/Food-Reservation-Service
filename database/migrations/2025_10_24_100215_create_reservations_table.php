<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('center_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 12, 0)->default(0); // مبلغ کل به ریال (integer-like)
            $table->date('reservation_date')->nullable(); // اختیاری: تاریخ اولین رزرو یا null
            $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('pending');
            $table->timestamp('reserved_at')->useCurrent(); // زمان ثبت رزرو
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservations');
    }
};
