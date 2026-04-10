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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('difficulty')->default('simple');
            $table->unsignedInteger('minimum_duration')->default(5)->comment('Minutes');
            $table->unsignedInteger('points_value')->default(5);
            $table->string('status')->default('pending');
            $table->unsignedInteger('repetition_count')->default(0);
            $table->decimal('repetition_decay', 5, 2)->default(1.00)->comment('Multiplier that decreases after 10 repeats');
            $table->date('scheduled_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['objective_id', 'status']);
            $table->index('scheduled_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
