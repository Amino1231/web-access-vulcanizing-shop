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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unique(['tenant_id', 'slug']);
            $table->string('attachment')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('archived_at')->nullable();
            $table->index(['tenant_id', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
