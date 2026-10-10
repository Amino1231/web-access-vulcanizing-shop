<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('appointment_number')->unique();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('start_time');
            $table->index('created_at');
            $table->index(['tenant_id', 'start_time']);
            $table->index(['customer_id', 'service_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};