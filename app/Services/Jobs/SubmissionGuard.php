<?php

namespace App\Services\Jobs;

use App\Models\JobApplication;

final class SubmissionGuard
{
    public function blockers(JobApplication $application, array $runtimeSignals = []): array
    {
        $application->loadMissing('opportunity', 'questions', 'accountTasks');
        $blockers = [];
        if ($application->status !== 'approved_for_submission') {
            $blockers[] = 'Current final submission approval is required.';
        }
        if (! app(ApplicationApproval::class)->isCurrent($application)) {
            $blockers[] = 'Approved hashes do not match the current package.';
        }
        if (! in_array($application->opportunity->source_status, ['open', 'verified_open'], true) || $application->opportunity->application_deadline?->isPast()) {
            $blockers[] = 'The job is not verified open.';
        }
        if ($application->questions->where('requires_user_confirmation', true)->whereNull('confirmed_at')->isNotEmpty()) {
            $blockers[] = 'Required questions remain unconfirmed.';
        }
        if ($application->questions->where('legal_or_sensitive', true)->whereNull('confirmed_at')->isNotEmpty()) {
            $blockers[] = 'A legal or sensitive declaration remains unresolved.';
        }
        if ($application->accountTasks->whereNotIn('verification_status', ['not_required', 'verified', 'complete'])->isNotEmpty()) {
            $blockers[] = 'Account or email verification is incomplete.';
        }
        if (! filter_var($application->final_application_url, FILTER_VALIDATE_URL)) {
            $blockers[] = 'The final application URL is not verified.';
        }
        foreach (['password_required', 'email_verification', 'captcha', 'mfa', 'ambiguous_legal_consent', 'demographic_questions', 'identity_documents', 'background_check_consent', 'salary_changed', 'posting_changed'] as $signal) {
            if (! empty($runtimeSignals[$signal])) {
                $blockers[] = 'User action required: '.str($signal)->replace('_', ' ')->toString().'.';
            }
        }

        return array_values(array_unique($blockers));
    }

    public function ready(JobApplication $application, array $runtimeSignals = []): bool
    {
        return $this->blockers($application, $runtimeSignals) === [];
    }
}
