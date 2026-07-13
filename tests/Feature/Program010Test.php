<?php

namespace Tests\Feature;

use App\Models\CandidateProfile;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Program010Test extends TestCase
{
    use RefreshDatabase;

    private const LINKEDIN_URL = 'https://www.linkedin.com/in/dimovskialeksandar/';

    public function test_public_identity_surfaces_use_the_configured_linkedin_url_once_per_component(): void
    {
        foreach (['/contact', '/resume'] as $uri) {
            $response = $this->get($uri)->assertOk()->assertSee(self::LINKEDIN_URL, false);

            // One page-specific link, one shared footer link, and one Person JSON-LD value.
            $this->assertSame(3, substr_count($response->getContent(), self::LINKEDIN_URL));
        }

        $contact = $this->get('/contact');
        $contact->assertSee('View Aleksandar Dimovski on LinkedIn', false);
        $contact->assertSee('github.com/gunnero')->assertSee(config('portfolio.email'))->assertSee(config('portfolio.phone'));
    }

    public function test_person_json_ld_contains_unique_github_and_linkedin_same_as_values(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $schemas = array_map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
        $person = collect($schemas)->firstWhere('@type', 'Person');

        $this->assertSame([config('portfolio.github'), self::LINKEDIN_URL], $person['sameAs']);
        $this->assertSame($person['sameAs'], array_values(array_unique($person['sameAs'])));
    }

    public function test_linkedin_is_not_a_site_route_or_exposed_as_workspace_metadata(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('linkedin.com')->assertDontSee('/workspace');
        $this->get('/workspace/profile')->assertRedirect('/workspace/login');
        $this->assertSame(0, JobApplication::query()->count());
    }

    public function test_candidate_profile_initialization_stores_linkedin_as_verified_without_creating_applications(): void
    {
        $user = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.test',
            'email_verified_at' => now(),
            'password' => Hash::make('secret-pass'),
            'is_workspace_owner' => true,
        ]);

        $this->artisan('jobs:initialize-profile', ['--user' => $user->id])->assertSuccessful();

        $profile = CandidateProfile::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(self::LINKEDIN_URL, $profile->linkedin_url);
        $this->assertSame('verified', $profile->field_states_json['linkedin_url']);
        $this->assertSame(0, JobApplication::query()->count());
    }

    public function test_pdf_resume_contains_a_clickable_linkedin_annotation_and_remains_two_page_a4(): void
    {
        $pdf = file_get_contents(public_path('files/aleksandar-dimovski-resume.pdf'));

        $this->assertStringContainsString('/URI ('.self::LINKEDIN_URL.')', $pdf);
        $this->assertSame(2, substr_count($pdf, '/MediaBox [0 0 594.95996 841.91998]'));
        $this->assertStringContainsString('/Count 2', $pdf);
    }

    public function test_only_the_verified_linkedin_profile_url_is_present_in_owned_sources(): void
    {
        $files = collect([
            base_path('config/portfolio.php'),
            resource_path('views/pages/contact.blade.php'),
            resource_path('views/pages/resume.blade.php'),
            resource_path('views/components/footer.blade.php'),
            resource_path('views/components/layout.blade.php'),
            app_path('Console/Commands/InitializeCandidateProfile.php'),
        ]);
        $content = $files->map(fn (string $file) => file_get_contents($file))->implode("\n");
        preg_match_all('#https://(?:www\.)?linkedin\.com/in/[^\s\'\"<)]+#', $content, $matches);

        $this->assertNotEmpty($matches[0]);
        foreach ($matches[0] as $url) {
            $this->assertSame(self::LINKEDIN_URL, $url);
        }
    }
}
