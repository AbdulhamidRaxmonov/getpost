<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('terminal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('shift_number')->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->decimal('opening_cash', 15, 2)->default(0)->comment('Ochilish saldosi');
            $table->decimal('closing_cash', 15, 2)->nullable()->comment('Yopilish saldosi');
            $table->decimal('expected_cash', 15, 2)->default(0)->comment('Kutilgan saldo');
            $table->decimal('difference', 15, 2)->default(0)->comment('Farq');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->text('closing_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
