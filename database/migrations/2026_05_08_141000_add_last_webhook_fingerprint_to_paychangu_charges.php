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
        Schema::table('paychangu_charges', function (Blueprint $table): void {
            $table->string('last_webhook_fingerprint', 64)->nullable()->after('provider_webhook_payload');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paychangu_charges', function (Blueprint $table): void {
            $table->dropColumn('last_webhook_fingerprint');
        });
    }
};
