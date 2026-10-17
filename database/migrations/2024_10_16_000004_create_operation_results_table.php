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
        Schema::create('operation_results', function (Blueprint $table) {
            $table->id();
            $table->uuid('managed_application_id');
            $table->foreign('managed_application_id')->references('id')->on('managed_applications')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Operation details
            $table->string('operation');
            $table->json('parameters')->nullable();
            $table->string('correlation_id')->unique();
            
            // Status tracking
            $table->enum('status', ['pending', 'running', 'succeeded', 'failed', 'timed_out', 'cancelled'])->default('pending');
            $table->text('result')->nullable();
            $table->string('error_type')->nullable();
            $table->text('error_message')->nullable();
            
            // Timing
            $table->integer('duration_ms')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('timed_out_at')->nullable();
            
            // Metadata
            $table->string('initiated_by')->default('manual'); // manual, scheduler, api
            $table->boolean('requires_confirmation')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('managed_application_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('correlation_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_results');
    }
};
