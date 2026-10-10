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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku');
            $table->unique(['tenant_id', 'slug']);
            $table->unique(['tenant_id', 'sku']);
            $table->string('barcode')->nullable();
            $table->decimal('cost_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->integer('stock')->default(0);
            $table->integer('low_stock_alert')->default(5);
            $table->text('description')->nullable();
            $table->string('ft_img')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
