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
        Schema::create('health_checks', function (Blueprint $table) {
            $table->id();
            $table->uuid('managed_application_id');
            $table->foreign('managed_application_id')->references('id')->on('managed_applications')->cascadeOnDelete();
            
            // Health status
            $table->enum('status', ['healthy', 'degraded', 'unreachable', 'unknown'])->default('unknown');
            $table->string('message')->nullable();
            
            // Check details
            $table->json('checks')->nullable(); // application, database, cache status
            $table->integer('response_time_ms')->nullable();
            $table->string('connector_version')->nullable();
            
            // Error tracking
            $table->string('error_type')->nullable();
            $table->text('error_message')->nullable();
            
            // Metadata
            $table->string('checked_by')->default('manual'); // manual, scheduler, api
            $table->timestamp('checked_at');
            
            $table->timestamps();
            
            // Indexes
            $table->index('managed_application_id');
            $table->index('status');
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_checks');
    }
};
