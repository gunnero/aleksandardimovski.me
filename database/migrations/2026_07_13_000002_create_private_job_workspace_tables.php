<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_profiles', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            foreach (['full_name', 'professional_email', 'phone', 'location', 'timezone', 'portfolio_url', 'github_url', 'primary_title', 'secondary_title', 'salary_currency', 'salary_period', 'notice_period', 'availability', 'remote_preference', 'employment_preference'] as $c) {
                $t->text($c)->nullable();
            }
            foreach (['professional_summary', 'work_authorization_notes', 'education_json', 'languages_json', 'verified_skills_json', 'employment_history_json', 'standard_answers_json', 'field_states_json'] as $c) {
                $t->longText($c)->nullable();
            }
            // Text columns are required because Laravel encrypted casts store ciphertext.
            $t->text('salary_minimum')->nullable();
            $t->text('salary_target')->nullable();
            $t->timestamps();
        });
        Schema::create('job_opportunities', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            foreach (['company_name', 'role_title', 'original_url', 'normalized_url', 'remote_scope', 'location', 'employment_type', 'salary_currency', 'salary_period', 'location_eligibility', 'international_contracting', 'salary_recommendation', 'discovered_by'] as $c) {
                $t->text($c)->nullable();
            }
            $t->char('normalized_url_hash', 64)->nullable();
            $t->string('source', 100)->nullable();
            $t->string('source_status', 64)->nullable();
            $t->string('review_status', 64)->nullable();
            $t->string('external_job_id')->nullable();
            $t->date('posting_date')->nullable();
            $t->timestamp('discovered_at');
            $t->date('application_deadline')->nullable();
            $t->timestamp('source_verified_at')->nullable();
            $t->decimal('salary_min', 12, 2)->nullable();
            $t->decimal('salary_max', 12, 2)->nullable();
            foreach (['fit_score', 'technical_fit_score', 'seniority_fit_score', 'remote_fit_score', 'compensation_fit_score', 'product_fit_score', 'evidence_fit_score', 'application_effort_score'] as $c) {
                $t->unsignedTinyInteger($c)->nullable();
            }
            foreach (['technology_stack_json', 'required_experience_json', 'preferred_experience_json', 'job_description', 'company_summary', 'strengths_json', 'gaps_json', 'risks_json', 'review_notes'] as $c) {
                $t->longText($c)->nullable();
            }
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->string('rejection_reason')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'normalized_url_hash']);
            $t->unique(['user_id', 'source', 'external_job_id']);
            $t->index(['user_id', 'review_status']);
        });
        Schema::create('job_applications', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('job_opportunity_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('status')->default('preparing_application');
            foreach (['selected_canonical_cv', 'tailored_cv_path', 'cover_letter_path', 'salary_answer', 'notice_period_answer', 'work_authorization_answer', 'required_account', 'application_method', 'final_application_url', 'confirmation_number', 'confirmation_url', 'submission_agent', 'screenshot_path'] as $c) {
                $t->text($c)->nullable();
            }
            foreach (['application_answers', 'attachments', 'preparation_notes', 'unresolved_questions', 'approved_application_hash', 'approved_document_hashes', 'approval_note', 'submitted_document_hashes', 'submitted_answers_hash'] as $c) {
                $t->longText($c)->nullable();
            }
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->boolean('confirmation_email_expected')->default(false);
            $t->date('next_follow_up_date')->nullable();
            $t->timestamps();
        });
        Schema::create('application_questions', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $t->text('question');
            $t->longText('answer')->nullable();
            $t->string('answer_source')->nullable();
            $t->decimal('confidence', 5, 2)->nullable();
            $t->boolean('requires_user_confirmation')->default(false);
            $t->timestamp('confirmed_at')->nullable();
            $t->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->boolean('legal_or_sensitive')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('application_account_tasks', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $t->string('provider')->nullable();
            $t->string('account_email')->nullable();
            $t->timestamp('account_created_at')->nullable();
            $t->string('verification_status')->default('not_required');
            $t->timestamp('verification_requested_at')->nullable();
            $t->timestamp('verification_completed_at')->nullable();
            $t->text('login_url')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('email_events', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $t->string('event_type');
            $t->string('sender')->nullable();
            $t->text('subject')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->string('external_message_id')->nullable();
            $t->boolean('action_required')->default(false);
            $t->timestamp('processed_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('agent_activities', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('job_opportunity_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $t->string('event_type');
            $t->string('agent_source')->nullable();
            $t->json('metadata')->nullable();
            $t->timestamp('occurred_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['agent_activities', 'email_events', 'application_account_tasks', 'application_questions', 'job_applications', 'job_opportunities', 'candidate_profiles'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
