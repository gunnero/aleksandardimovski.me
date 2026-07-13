<?php

namespace App\Services\Jobs;

use App\Models\JobApplication;

final class ApplicationPreparationGuard
{
    public function blockers(JobApplication $application): array
    {
        $application->loadMissing('opportunity', 'questions');
        $blockers = [];
        if ($application->opportunity->review_status !== 'approved_for_preparation') {
            $blockers[] = 'The opportunity is not approved for preparation.';
        }
        if (! in_array($application->opportunity->source_status, ['open', 'verified_open'], true) || $application->opportunity->application_deadline?->isPast()) {
            $blockers[] = 'The source posting must be reverified open.';
        }
        if (in_array(strtolower((string) $application->opportunity->location_eligibility), ['', 'unknown', 'unclear', 'ineligible'], true)) {
            $blockers[] = 'Remote and geographic eligibility must be verified.';
        }
        foreach (['tailored_cv_path' => 'Tailored CV', 'cover_letter_path' => 'Cover letter', 'application_answers' => 'Application answers', 'salary_answer' => 'Salary recommendation', 'preparation_notes' => 'Interview and role research'] as $field => $label) {
            if (blank($application->{$field})) {
                $blockers[] = "{$label} is incomplete.";
            }
        }
        if ($application->questions->where('requires_user_confirmation', true)->whereNull('confirmed_at')->isNotEmpty()) {
            $blockers[] = 'Unresolved questions require user input.';
        }

        return $blockers;
    }

    public function readyForFinalReview(JobApplication $application): bool
    {
        return $this->blockers($application) === [];
    }
}
