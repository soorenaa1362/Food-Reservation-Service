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

            /* ---------- Relations ---------- */

            $table->foreignId('transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('center_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_card_id')->constrained()->cascadeOnDelete();

            /* ---------- Ledger math ---------- */

            $table->bigInteger('amount'); // signed
            $table->bigInteger('balance_before');
            $table->bigInteger('balance_after');

            $table->tinyInteger('type'); // 1=increase, 2=decrease

            /* ---------- Business source ---------- */

            $table->tinyInteger('source_type'); // payment/reservation/manual
            $table->unsignedBigInteger('source_id')->nullable();

            /* ---------- Sync / idempotency ---------- */

            // origin of ledger entry
            // 1=local service, 2=his, 3=system
            $table->tinyInteger('origin')->default(1);

            // unique id from HIS or local UUID
            $table->string('external_id')->nullable();

            // duplicate protection
            $table->unique(['origin', 'external_id']);

            // sync tracking
            $table->timestamp('received_from_his_at')->nullable();
            $table->timestamp('sent_to_his_at')->nullable();

            /* ---------- Metadata ---------- */

            $table->string('description')->nullable();
            $table->json('meta')->nullable();

            /* ---------- Performance indexes ---------- */

            $table->index(['credit_card_id', 'id']);
            $table->index(['user_id', 'created_at']);
            $table->index('origin');

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
