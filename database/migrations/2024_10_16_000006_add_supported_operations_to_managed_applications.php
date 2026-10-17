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
        Schema::table('managed_applications', function (Blueprint $table) {
            $table->json('supported_operations')->nullable()->after('capabilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('managed_applications', function (Blueprint $table) {
            $table->dropColumn('supported_operations');
        });
    }
};
