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
        Schema::create('operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_result_id')->constrained()->cascadeOnDelete();
            
            // Log details
            $table->enum('level', ['info', 'warning', 'error', 'debug'])->default('info');
            $table->text('message');
            $table->json('context')->nullable();
            
            $table->timestamp('logged_at');
            
            $table->timestamps();
            
            // Indexes
            $table->index('operation_result_id');
            $table->index('level');
            $table->index('logged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_logs');
    }
};
