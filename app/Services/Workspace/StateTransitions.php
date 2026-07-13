<?php

namespace App\Services\Workspace;

use App\Models\JobApplication;
use App\Models\JobOpportunity;
use App\Services\Jobs\ApplicationApproval;
use Illuminate\Validation\ValidationException;

final class StateTransitions
{
    private const JOB = [
        'discovered' => ['needs_review', 'duplicate', 'expired'],
        'needs_review' => ['approved_for_preparation', 'rejected', 'saved_for_later', 'needs_research', 'duplicate', 'expired'],
        'saved_for_later' => ['needs_review', 'approved_for_preparation', 'rejected', 'expired'],
        'needs_research' => ['needs_review', 'approved_for_preparation', 'rejected', 'saved_for_later', 'duplicate', 'expired'],
        'approved_for_preparation' => ['expired'], 'rejected' => [], 'duplicate' => [], 'expired' => [],
    ];

    private const APPLICATION = [
        'preparing_application' => ['needs_user_input', 'ready_for_final_review', 'closed', 'withdrawn'],
        'needs_user_input' => ['preparing_application', 'ready_for_final_review', 'closed', 'withdrawn'],
        'ready_for_final_review' => ['preparing_application', 'approved_for_submission', 'closed', 'withdrawn'],
        'approved_for_submission' => ['ready_for_final_review', 'submitting', 'withdrawn'],
        'submitting' => ['submitted', 'submission_failed', 'verification_pending'],
        'submission_failed' => ['preparing_application', 'ready_for_final_review', 'closed'],
        'verification_pending' => ['submitted', 'submission_failed'],
        'submitted' => ['follow_up_due', 'interview', 'technical_test', 'offer', 'rejected_by_company', 'withdrawn', 'closed'],
        'follow_up_due' => ['interview', 'technical_test', 'offer', 'rejected_by_company', 'withdrawn', 'closed'],
        'interview' => ['technical_test', 'offer', 'rejected_by_company', 'withdrawn', 'closed'],
        'technical_test' => ['interview', 'offer', 'rejected_by_company', 'withdrawn', 'closed'],
        'offer' => ['withdrawn', 'closed'], 'rejected_by_company' => ['closed'], 'withdrawn' => ['closed'], 'closed' => [],
    ];

    public function job(JobOpportunity $job, string $to): void
    {
        $this->assertAllowed(self::JOB, $job->review_status, $to);
    }

    public function application(JobApplication $application, string $to): void
    {
        $this->assertAllowed(self::APPLICATION, $application->status, $to);
    }

    public function beginSubmission(JobApplication $application, ApplicationApproval $approval): void
    {
        $this->application($application, 'submitting');
        if (! $approval->isCurrent($application) || $application->questions()->where('requires_user_confirmation', true)->whereNull('confirmed_at')->exists() || ! in_array($application->opportunity->source_status, ['open', 'verified_open'], true)) {
            throw ValidationException::withMessages(['status' => 'Submission prerequisites are no longer satisfied.']);
        }
        $application->status = 'submitting';
        $application->save();
    }

    private function assertAllowed(array $map, ?string $from, string $to): void
    {
        if (! in_array($to, $map[$from] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "Invalid transition from {$from} to {$to}."]);
        }
    }
}
