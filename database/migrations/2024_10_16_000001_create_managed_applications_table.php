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
        Schema::create('managed_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Application details
            $table->string('name');
            $table->string('base_url')->unique();
            $table->enum('environment', ['local', 'development', 'staging', 'production'])->default('production');
            $table->string('timezone')->default('UTC');
            $table->string('description')->nullable();
            
            // Connection status
            $table->enum('connection_status', ['pending', 'connected', 'disconnected', 'revoked'])->default('pending');
            $table->string('connector_version')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('last_health_check_at')->nullable();
            
            // Credentials (encrypted)
            $table->text('api_key');
            $table->text('api_secret');
            $table->text('webhook_secret')->nullable();
            
            // Security
            $table->boolean('verify_ssl')->default(true);
            $table->integer('timeout_seconds')->default(30);
            $table->integer('max_retries')->default(3);
            
            // Metadata
            $table->json('capabilities')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('connection_status');
            $table->index('environment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managed_applications');
    }
};
