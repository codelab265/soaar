<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paychangu_charges', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();

            $table->string('purpose'); // e.g. course_enrollment
            $table->string('payment_method'); // mobile_money | bank_transfer | card

            $table->string('charge_id')->unique();
            $table->string('ref_id')->nullable();

            $table->string('currency', 10)->default('MWK');
            $table->unsignedInteger('amount')->default(0);
            $table->unsignedInteger('points_reserved')->default(0);

            $table->string('status')->default('pending'); // pending | success | failed

            $table->json('provider_initialize_response')->nullable();
            $table->json('provider_verify_response')->nullable();
            $table->json('provider_webhook_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paychangu_charges');
    }
};
