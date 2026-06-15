<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sku')->comment('Artikul - mahsulot kodi');
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0)->comment('Sotib olish narxi');
            $table->decimal('selling_price', 15, 2)->default(0)->comment('Sotish narxi');
            $table->decimal('min_price', 15, 2)->default(0)->comment('Minimal narx');
            $table->decimal('vat_percent', 5, 2)->default(0)->comment('QQS foizi');
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true)->comment('Ombor hisobi');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
