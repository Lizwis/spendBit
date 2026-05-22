<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();

            $table->string('wallet');
            $table->string('tx_hash')->unique();

            $table->decimal('amount', 36, 18)->default(0);

            $table->string('token_address');
            $table->string('network')->default('polygon-amoy');

            $table->string('status')->default('confirmed');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
