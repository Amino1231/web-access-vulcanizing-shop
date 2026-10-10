<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();

            // What the review is about — exactly ONE of these will be set
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();

            // ─── Moderation ──────────────────────────
            $table->boolean('is_hidden')->default(false);
            $table->timestamp('flagged_at')->nullable();
            $table->timestamp('hidden_at')->nullable();

            $table->timestamps();

            // Indexes for the common queries
            $table->index(['product_id', 'created_at']);
            $table->index(['customer_id', 'product_id']);
            $table->index(['tenant_id', 'created_at']);
            $table->index('is_hidden');
            $table->index('flagged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};