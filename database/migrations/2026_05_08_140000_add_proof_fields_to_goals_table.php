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
        Schema::table('goals', function (Blueprint $table): void {
            $table->text('proof_request_message')->nullable()->after('accountability_partner_id');
            $table->text('proof_submission')->nullable()->after('proof_request_message');
            $table->timestamp('proof_requested_at')->nullable()->after('proof_submission');
            $table->timestamp('proof_submitted_at')->nullable()->after('proof_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table): void {
            $table->dropColumn([
                'proof_request_message',
                'proof_submission',
                'proof_requested_at',
                'proof_submitted_at',
            ]);
        });
    }
};
