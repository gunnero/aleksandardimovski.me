<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_preference_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('rule_type', 64);
            $table->string('rule_key', 100);
            $table->string('operator', 40);
            $table->longText('comparison_value_json');
            $table->string('severity', 32);
            $table->string('scope', 32)->default('all_jobs');
            $table->foreignId('source_job_opportunity_id')->nullable()->constrained('job_opportunities')->nullOnDelete();
            $table->foreignId('source_review_history_id')->nullable()->constrained('opportunity_review_histories')->nullOnDelete();
            $table->longText('reason');
            $table->decimal('confidence', 5, 2)->default(1);
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'active', 'severity']);
        });

        Schema::create('job_rule_evaluations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_opportunity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('preference_rule_id')->constrained('job_preference_rules')->cascadeOnDelete();
            $table->boolean('matched');
            $table->smallInteger('score_adjustment')->default(0);
            $table->string('decision', 32);
            $table->longText('explanation');
            $table->timestamp('evaluated_at');
            $table->timestamps();
            $table->unique(['job_opportunity_id', 'preference_rule_id'], 'job_rule_eval_opp_rule_uq');
        });

        Schema::table('job_opportunities', function (Blueprint $table): void {
            $table->unsignedTinyInteger('base_fit_score')->nullable()->after('fit_score');
            $table->smallInteger('preference_adjustment')->default(0)->after('base_fit_score');
            $table->string('preference_decision', 32)->default('allowed')->after('preference_adjustment');
        });
    }

    public function down(): void
    {
        Schema::table('job_opportunities', fn (Blueprint $table) => $table->dropColumn(['base_fit_score', 'preference_adjustment', 'preference_decision']));
        Schema::table('job_rule_evaluations', function (Blueprint $table): void {
            $table->dropForeign(['job_opportunity_id']);
            $table->dropForeign(['preference_rule_id']);
            $table->dropUnique('job_rule_eval_opp_rule_uq');
        });
        Schema::dropIfExists('job_rule_evaluations');
        Schema::dropIfExists('job_preference_rules');
    }
};
