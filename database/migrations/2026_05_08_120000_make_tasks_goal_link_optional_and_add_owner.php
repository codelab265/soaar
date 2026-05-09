<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->after('objective_id')->constrained()->nullOnDelete();
            $table->foreign('objective_id')->references('id')->on('objectives')->nullOnDelete();
        });

        DB::table('tasks')->update([
            'goal_id' => DB::raw('(select objectives.goal_id from objectives where objectives.id = tasks.objective_id)'),
            'user_id' => DB::raw('(select goals.user_id from goals where goals.id = (select objectives.goal_id from objectives where objectives.id = tasks.objective_id))'),
        ]);

        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreignId('objective_id')->nullable()->change();
            $table->index(['user_id', 'scheduled_date']);
            $table->index(['goal_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'scheduled_date']);
            $table->dropIndex(['goal_id', 'status']);
            $table->dropConstrainedForeignId('goal_id');
            $table->dropConstrainedForeignId('user_id');
            $table->foreign('objective_id')->references('id')->on('objectives')->cascadeOnDelete();
            $table->foreignId('objective_id')->nullable(false)->change();
        });
    }
};
