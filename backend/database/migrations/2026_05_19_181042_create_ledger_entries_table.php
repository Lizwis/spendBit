<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('type');
            // crypto_deposit | card_funding | spend | refund

            $table->string('reference_id')->nullable()->index();
            // tx hash / stripe id

            $table->enum('direction', ['credit', 'debit']);

            $table->decimal('amount', 18, 6);

            $table->string('currency')->default('USDC');

            $table->enum('status', ['pending', 'confirmed', 'failed'])
                ->default('confirmed');

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
