<?php

namespace App\Models;

use App\Services\Workspace\PrivateApplicationDocuments;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    private bool $approvalWasInvalidated = false;

    protected $guarded = ['id', 'user_id', 'approved_by', 'approved_at', 'approved_application_hash', 'approved_document_hashes'];

    protected function casts(): array
    {
        return ['application_answers' => 'encrypted:array', 'attachments' => 'encrypted:array', 'unresolved_questions' => 'encrypted:array', 'approved_document_hashes' => 'array', 'submitted_document_hashes' => 'array', 'confirmation_email_expected' => 'boolean', 'approved_at' => 'datetime', 'submitted_at' => 'datetime', 'next_follow_up_date' => 'date'];
    }

    public function opportunity()
    {
        return $this->belongsTo(JobOpportunity::class, 'job_opportunity_id');
    }

    public function questions()
    {
        return $this->hasMany(ApplicationQuestion::class);
    }

    public function accountTasks()
    {
        return $this->hasMany(ApplicationAccountTask::class);
    }

    protected static function booted(): void
    {
        static::saving(function (self $application): void {
            if ($application->approved_at && $application->isDirty(['selected_canonical_cv', 'tailored_cv_path', 'cover_letter_path', 'application_answers', 'salary_answer', 'notice_period_answer', 'work_authorization_answer', 'attachments', 'final_application_url'])) {
                $application->status = 'ready_for_final_review';
                $application->approved_by = null;
                $application->approved_at = null;
                $application->approved_application_hash = null;
                $application->approved_document_hashes = null;
                $application->approvalWasInvalidated = true;
            }
        });
        static::saved(function (self $application): void {
            if ($application->approvalWasInvalidated) {
                AgentActivity::create(['user_id' => $application->user_id, 'job_opportunity_id' => $application->job_opportunity_id, 'job_application_id' => $application->id, 'event_type' => 'approval_invalidated', 'agent_source' => 'workspace', 'occurred_at' => now()]);
                $application->approvalWasInvalidated = false;
            }
        });
        static::deleted(fn (self $application) => app(PrivateApplicationDocuments::class)->deleteAll($application));
    }
}
