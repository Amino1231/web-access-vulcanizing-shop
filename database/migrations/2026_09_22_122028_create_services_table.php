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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_vehicle_service')->default(false);  
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
