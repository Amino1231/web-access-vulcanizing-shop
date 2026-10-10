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
        Schema::create('road_side_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('request_number')->unique();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('address')->nullable();
            $table->string('problem_type');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'accepted', 'completed'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('road_side_requests');
    }
};
