<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cash transactions (Kassa harakatlari)
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out'])->comment('Kirim/Chiqim');
            $table->decimal('amount', 15, 2);
            $table->string('reason')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Currency rates
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 3)->default('USD');
            $table->decimal('rate', 15, 2)->comment('1 USD = ? UZS');
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
        Schema::dropIfExists('currency_rates');
    }
};
