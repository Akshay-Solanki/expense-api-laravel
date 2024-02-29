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
            $table->ulid()->primary();
            $table->foreignUlid('account_ulid');
            $table->foreignUlid('to_account_ulid')->nullable();
            $table->foreignUlid('category_ulid');
            $table->foreignUlid('user_ulid');
            $table->datetime('date');
            $table->unsignedBigInteger('amount');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
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
