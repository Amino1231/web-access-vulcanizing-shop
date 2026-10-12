<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('roadside_request_id')->nullable()->constrained('road_side_requests')->nullOnDelete();
            $table->foreignId('served_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2);

            // ─── Commission split ────────────────
            $table->decimal('platform_commission', 12, 2)->default(0);
            $table->decimal('shop_net_revenue', 12, 2)->default(0);
            $table->decimal('commission_rate_applied', 5, 2)->default(0);

            $table->string('payment_method');
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};