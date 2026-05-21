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
        Schema::create('crypto_deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('tx_hash')->unique();

            $table->string('from_address');
            $table->string('to_address');

            $table->decimal('amount', 18, 6);

            $table->string('currency')->default('USDC');

            $table->string('chain')->default('polygon');

            $table->integer('confirmations')->default(0);

            $table->enum('status', ['pending', 'confirmed'])
                ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_deposits');
    }
};
