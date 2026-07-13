<?php

namespace App\Services\Jobs;

use App\Models\AgentActivity;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class ApplicationApproval
{
    public function contentHash(JobApplication $a): string
    {
        return hash('sha256', json_encode(['answers' => $a->application_answers, 'salary' => $a->salary_answer, 'notice' => $a->notice_period_answer, 'authorization' => $a->work_authorization_answer, 'questions' => $a->questions()->orderBy('sort_order')->get(['question', 'answer', 'confirmed_at'])->toArray(), 'url' => $a->final_application_url], JSON_UNESCAPED_SLASHES));
    }

    public function documentHashes(JobApplication $a): array
    {
        $paths = array_filter(array_merge([$a->selected_canonical_cv, $a->tailored_cv_path, $a->cover_letter_path], $a->attachments ?? []));
        $disk = Storage::disk('local');
        $out = [];
        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $out[$path] = hash('sha256', $disk->get($path));
            }
        }

return $out;
    }

    public function approve(JobApplication $a, User $user, ?string $note): void
    {
        $a->forceFill(['status' => 'approved_for_submission', 'approved_by' => $user->id, 'approved_at' => now(), 'approved_application_hash' => $this->contentHash($a), 'approved_document_hashes' => $this->documentHashes($a), 'approval_note' => $note])->save();
        $this->audit($a, 'approval_granted');
    }

    public function isCurrent(JobApplication $a): bool
    {
        return hash_equals((string) $a->approved_application_hash, $this->contentHash($a)) && ($a->approved_document_hashes ?? []) === $this->documentHashes($a);
    }

    public function invalidate(JobApplication $a): void
    {
        if (! $a->approved_at) {
            return;
        } $a->forceFill(['status' => 'ready_for_final_review', 'approved_by' => null, 'approved_at' => null, 'approved_application_hash' => null, 'approved_document_hashes' => null])->save();
        $this->audit($a, 'approval_invalidated');
    }

    private function audit(JobApplication $a, string $event): void
    {
        AgentActivity::create(['user_id' => $a->user_id, 'job_opportunity_id' => $a->job_opportunity_id, 'job_application_id' => $a->id, 'event_type' => $event, 'agent_source' => 'workspace', 'occurred_at' => now()]);
    }
}
