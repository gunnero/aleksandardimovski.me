<?php

namespace App\Services\Jobs;

use App\Models\JobPreferenceRule;
use App\Models\User;
use Illuminate\Support\Str;

final class PreferenceRuleEvaluator
{
    public function evaluate(User $user, array $job): array
    {
        $base = max(0, min(100, (int) ($job['fit_score'] ?? 0)));
        $adjustment = 0;
        $decision = $this->unclearEligibility($job) ? 'needs_research' : 'allowed';
        $evaluations = [];

        foreach (JobPreferenceRule::where('user_id', $user->id)->applicable()->get() as $rule) {
            if (! $this->inScope($rule, $job) || ! $this->matches($rule, $job)) {
                continue;
            }
            $penalty = match ($rule->severity) {
                'strong_penalty' => -20, 'soft_penalty' => -8, default => 0
            };
            $ruleDecision = $rule->severity === 'hard_exclusion' ? 'excluded' : ($decision === 'needs_research' ? 'needs_research' : 'allowed');
            if ($ruleDecision === 'excluded') {
                $decision = 'excluded';
            }
            $adjustment += $penalty;
            $evaluations[] = ['preference_rule_id' => $rule->id, 'matched' => true, 'score_adjustment' => $penalty, 'decision' => $ruleDecision, 'explanation' => $this->explanation($rule), 'evaluated_at' => now()];
        }

        return ['base_fit_score' => $base, 'preference_adjustment' => $adjustment, 'final_fit_score' => max(0, min(100, $base + $adjustment)), 'decision' => $decision, 'evaluations' => $evaluations];
    }

    private function matches(JobPreferenceRule $rule, array $job): bool
    {
        $actual = match ($rule->rule_key) {
            'annual_remote_limit' => $job['remote_scope'] ?? '',
            'location_eligibility' => $job['location_eligibility'] ?? '',
            'salary_minimum' => $job['salary_max'] ?? $job['salary_min'] ?? null,
            'seniority' => $job['role_title'] ?? '',
            'technology' => implode(' ', $job['technology_stack_json'] ?? []),
            'employment_type' => $job['employment_type'] ?? '',
            default => implode(' ', array_filter([$job['role_title'] ?? null, $job['job_description'] ?? null, $job['remote_scope'] ?? null])),
        };
        $expected = $rule->comparison_value_json;
        $values = is_array($expected) ? ($expected['values'] ?? $expected['value'] ?? $expected) : [$expected];
        $values = is_array($values) ? $values : [$values];

        if ($rule->rule_type === 'remote_policy' && $rule->rule_key === 'annual_remote_limit') {
            return $this->matchesRestrictedRemotePolicy((string) $actual);
        }

        return match ($rule->operator) {
            'contains_any' => collect($values)->contains(fn ($value) => Str::contains(Str::lower((string) $actual), Str::lower((string) $value))),
            'not_contains_any' => ! collect($values)->contains(fn ($value) => Str::contains(Str::lower((string) $actual), Str::lower((string) $value))),
            'less_than' => is_numeric($actual) && (float) $actual < (float) ($values[0] ?? 0),
            'equals' => Str::lower((string) $actual) === Str::lower((string) ($values[0] ?? '')),
            default => false,
        };
    }

    private function inScope(JobPreferenceRule $rule, array $job): bool
    {
        $value = $rule->comparison_value_json['scope_value'] ?? null;

        return match ($rule->scope) {
            'company' => $value && strcasecmp((string) ($job['company_name'] ?? ''), (string) $value) === 0,
            'role_family' => $value && Str::contains(Str::lower((string) ($job['role_title'] ?? '')), Str::lower((string) $value)),
            'source' => $value && strcasecmp((string) ($job['source'] ?? ''), (string) $value) === 0,
            'country' => $value && Str::contains(Str::lower((string) ($job['location'] ?? '')), Str::lower((string) $value)),
            'technology' => $value && Str::contains(Str::lower(implode(' ', $job['technology_stack_json'] ?? [])), Str::lower((string) $value)),
            default => true,
        };
    }

    private function unclearEligibility(array $job): bool
    {
        $location = Str::lower((string) ($job['location_eligibility'] ?? ''));
        $remote = Str::lower(trim((string) ($job['remote_scope'] ?? '')));
        $remoteIsClear = Str::contains($remote, ['fully remote', '100% remote', 'remote-first']) || $this->matchesRestrictedRemotePolicy($remote);

        return in_array($location, ['', 'unknown', 'unclear', 'needs research'], true)
            || ! $remoteIsClear;
    }

    private function matchesRestrictedRemotePolicy(string $policy): bool
    {
        $policy = Str::lower($policy);
        if (Str::contains($policy, ['optional office', 'office access is optional', 'voluntary office', 'voluntary company event', 'optional company event'])) {
            return false;
        }

        return Str::contains($policy, ['hybrid required', 'hybrid requirement', 'hybrid-only', 'office-first', 'limited remote', 'annual remote days'])
            || preg_match('/\bremote\s+(?:work\s+)?(?:is\s+)?limited\s+to\s+\d+\s+days?\s+per\s+year\b/', $policy) === 1
            || preg_match('/\b(?:regular|recurring|required|mandatory)\s+office\s+attendance\b/', $policy) === 1
            || preg_match('/\boffice\s+attendance\s+(?:is\s+)?(?:regular|required|mandatory)\b/', $policy) === 1;
    }

    private function explanation(JobPreferenceRule $rule): string
    {
        return match ($rule->severity) {
            'hard_exclusion' => "Excluded by confirmed {$rule->rule_type} rule: {$rule->reason}",
            'strong_penalty' => "20-point penalty from confirmed {$rule->rule_type} rule: {$rule->reason}",
            'soft_penalty' => "8-point penalty from confirmed {$rule->rule_type} rule: {$rule->reason}",
            default => "Matched informational {$rule->rule_type} rule: {$rule->reason}",
        };
    }
}
