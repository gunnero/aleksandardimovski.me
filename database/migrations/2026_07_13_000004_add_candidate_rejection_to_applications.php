<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('rejection_reason')->nullable()->after('status');
            $table->longText('rejection_note')->nullable()->after('rejection_reason');
            $table->foreignId('rejected_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
        });

        Schema::table('opportunity_review_histories', function (Blueprint $table): void {
            $table->string('rejection_reason')->nullable()->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('opportunity_review_histories', function (Blueprint $table): void {
            $table->dropColumn('rejection_reason');
        });

        Schema::table('job_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['rejection_reason', 'rejection_note', 'rejected_at']);
        });
    }
};
