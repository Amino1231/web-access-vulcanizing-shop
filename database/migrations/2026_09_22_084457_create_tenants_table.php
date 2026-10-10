<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('business_setup_completed')->default(false);
            $table->json('business_hours')->nullable();
            $table->json('attachment')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('verification_status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->json('missing_requirements')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'latitude', 'longitude']);
            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};