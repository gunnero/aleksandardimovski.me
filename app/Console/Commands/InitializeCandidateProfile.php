<?php

namespace App\Console\Commands;

use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Console\Command;

final class InitializeCandidateProfile extends Command
{
    protected $signature = 'jobs:initialize-profile {--user= : Existing workspace user email or ID}';

    protected $description = 'Initialize the private candidate profile from verified portfolio facts';

    public function handle(): int
    {
        if (! $this->option('user')) {
            $this->error('The --user option is required. No user is created automatically.');

            return self::INVALID;
        }
        $user = User::query()->where('email', $this->option('user'))->orWhere('id', $this->option('user'))->first();
        if (! $user) {
            $this->error('Workspace user not found.');

            return self::FAILURE;
        }
        $states = [];
        foreach (['full_name', 'professional_email', 'phone', 'location', 'portfolio_url', 'github_url', 'primary_title', 'professional_summary', 'education_json', 'languages_json', 'verified_skills_json', 'employment_history_json'] as $key) {
            $states[$key] = 'verified';
        } foreach (['work_authorization_notes', 'notice_period', 'availability', 'salary_minimum', 'salary_target', 'standard_answers_json'] as $key) {
            $states[$key] = 'user_confirmation_required';
        }
        $profile = CandidateProfile::firstOrNew(['user_id' => $user->id]);
        $profile->fill(['full_name' => config('portfolio.name'), 'professional_email' => config('portfolio.email'), 'phone' => config('portfolio.phone'), 'location' => config('portfolio.location'), 'portfolio_url' => rtrim(config('app.url'), '/'), 'github_url' => config('portfolio.github'), 'primary_title' => 'Senior PHP / Laravel Engineer and Backend & Product Engineer', 'professional_summary' => config('resume.summary'), 'education_json' => [config('resume.education')], 'languages_json' => config('resume.languages'), 'verified_skills_json' => config('resume.skill_groups'), 'employment_history_json' => config('resume.experience'), 'field_states_json' => $states]);
        $profile->user_id = $user->id;
        $profile->save();
        $this->info('Candidate profile initialized from verified portfolio facts. USER INPUT REQUIRED fields were not invented.');

        return self::SUCCESS;
    }
}
