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
        Schema::create('credit_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_card_id')->constrained()->cascadeOnDelete();

            $table->bigInteger('amount'); // + یا -
            $table->bigInteger('balance_before');
            $table->bigInteger('balance_after');

            $table->tinyInteger('type'); // 1=increase, 2=decrease
            $table->tinyInteger('source_type'); // 1=payment, 2=reservation, 3=manual
            $table->unsignedBigInteger('source_id')->nullable();

            $table->string('description')->nullable();
            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_ledgers');
    }
};
