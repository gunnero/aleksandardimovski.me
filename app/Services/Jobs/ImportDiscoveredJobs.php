<?php

namespace App\Services\Jobs;

use App\Models\AgentActivity;
use App\Models\JobOpportunity;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ImportDiscoveredJobs
{
    private const ALLOWED = ['company_name', 'role_title', 'original_url', 'source', 'external_job_id', 'posting_date', 'application_deadline', 'remote_scope', 'location', 'employment_type', 'salary_min', 'salary_max', 'salary_currency', 'salary_period', 'technology_stack_json', 'required_experience_json', 'preferred_experience_json', 'job_description', 'company_summary', 'source_verified_at', 'source_status', 'location_eligibility', 'international_contracting', 'fit_score', 'technical_fit_score', 'seniority_fit_score', 'remote_fit_score', 'compensation_fit_score', 'product_fit_score', 'evidence_fit_score', 'application_effort_score', 'strengths_json', 'gaps_json', 'risks_json', 'salary_recommendation', 'review_status', 'review_notes', 'discovered_by'];

    public function import(User $user, array $records, bool $dryRun = false): array
    {
        $report = ['total' => count($records), 'created' => 0, 'duplicates' => 0, 'warnings' => 0, 'invalid' => 0, 'dry_run' => $dryRun, 'items' => []];
        foreach ($records as $index => $input) {
            try {
                if (! is_array($input) || array_diff(array_keys($input), self::ALLOWED)) {
                    throw ValidationException::withMessages(['record' => 'Unknown or malformed fields.']);
                }
                $data = Validator::make($input, [
                    'company_name' => 'required|string|max:200', 'role_title' => 'required|string|max:200', 'original_url' => 'required|url:http,https|max:2048', 'source' => 'required|string|max:100', 'external_job_id' => 'nullable|string|max:200',
                    'posting_date' => 'nullable|date', 'application_deadline' => 'nullable|date', 'source_verified_at' => 'nullable|date', 'salary_min' => 'nullable|numeric|min:0', 'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
                    'salary_currency' => 'nullable|string|size:3', 'fit_score' => 'nullable|integer|between:0,100', 'technical_fit_score' => 'nullable|integer|between:0,100', 'seniority_fit_score' => 'nullable|integer|between:0,100', 'remote_fit_score' => 'nullable|integer|between:0,100', 'compensation_fit_score' => 'nullable|integer|between:0,100', 'product_fit_score' => 'nullable|integer|between:0,100', 'evidence_fit_score' => 'nullable|integer|between:0,100', 'application_effort_score' => 'nullable|integer|between:0,100',
                    'technology_stack_json' => 'nullable|array', 'required_experience_json' => 'nullable|array', 'preferred_experience_json' => 'nullable|array', 'strengths_json' => 'nullable|array', 'gaps_json' => 'nullable|array', 'risks_json' => 'nullable|array',
                    'review_status' => 'nullable|in:discovered,needs_review,needs_research',
                    '*' => 'nullable',
                ])->validate();
                $data['normalized_url'] = $this->normalizeUrl($data['original_url']);
                $data['normalized_url_hash'] = hash('sha256', $data['normalized_url']);
                if (JobOpportunity::where('user_id', $user->id)->where('normalized_url', $data['normalized_url'])->exists()) {
                    $report['duplicates']++;
                    $report['items'][] = ['index' => $index, 'status' => 'duplicate', 'url' => $data['normalized_url']];

                    continue;
                }
                if (! empty($data['external_job_id']) && JobOpportunity::where('user_id', $user->id)->where('source', $data['source'])->where('external_job_id', $data['external_job_id'])->exists()) {
                    $report['duplicates']++;
                    $report['items'][] = ['index' => $index, 'status' => 'duplicate_external_id'];

                    continue;
                }
                $similar = JobOpportunity::where('user_id', $user->id)->whereRaw('lower(company_name) = ?', [mb_strtolower($data['company_name'])])->get(['role_title']);
                $warnings = [];
                foreach ($similar as $candidate) {
                    similar_text(mb_strtolower($data['role_title']), mb_strtolower($candidate->role_title), $score);
                    if ($score >= 75) {
                        $warnings[] = 'Possible company/role duplicate';
                        break;
                    }
                }
                $report['warnings'] += count($warnings);
                if (! $dryRun) {
                    DB::transaction(function () use ($user, $data): void {
                        $job = new JobOpportunity(Arr::only($data, [...self::ALLOWED, 'normalized_url', 'normalized_url_hash']));
                        $job->user_id = $user->id;
                        $job->normalized_url = $data['normalized_url'];
                        $job->discovered_at = now();
                        $job->review_status = $data['review_status'] ?? 'needs_review';
                        $job->discovered_by = $data['discovered_by'] ?? 'codex';
                        $job->save();
                        AgentActivity::create(['user_id' => $user->id, 'job_opportunity_id' => $job->id, 'event_type' => 'record_imported', 'agent_source' => $job->discovered_by, 'metadata' => ['source' => $job->source], 'occurred_at' => now()]);
                    });
                }
                $report['created']++;
                $report['items'][] = ['index' => $index, 'status' => $dryRun ? 'would_create' : 'created', 'url' => $data['normalized_url'], 'warnings' => $warnings];
            } catch (ValidationException $e) {
                $report['invalid']++;
                $report['items'][] = ['index' => $index, 'status' => 'invalid', 'errors' => $e->errors()];
            }
        }

        return $report;
    }

    private function normalizeUrl(string $url): string
    {
        $parts = parse_url($url);
        if (! isset($parts['scheme'],$parts['host']) || ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw ValidationException::withMessages(['original_url' => 'Only HTTP(S) URLs are accepted.']);
        }
        $host = strtolower($parts['host']);
        if ($host === 'localhost' || str_ends_with($host, '.local') || (filter_var($host, FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            throw ValidationException::withMessages(['original_url' => 'Local and private-network URLs are not accepted.']);
        }
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        foreach (array_keys($query) as $key) {
            if (Str::startsWith(strtolower($key), ['utm_', 'fbclid', 'gclid'])) {
                unset($query[$key]);
            }
        }
        $path = rtrim($parts['path'] ?? '', '/');

        return 'https://'.$host.($parts['port'] ?? null ? ':'.$parts['port'] : '').$path.($query ? '?'.http_build_query($query) : '');
    }
}
