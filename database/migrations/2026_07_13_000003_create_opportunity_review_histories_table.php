<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_review_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('old_status', 64);
            $table->string('new_status', 64);
            $table->longText('review_note')->nullable();
            $table->string('action', 64)->default('decision');
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->index(['job_opportunity_id', 'reviewed_at'], 'review_history_job_reviewed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_review_histories');
    }
};
