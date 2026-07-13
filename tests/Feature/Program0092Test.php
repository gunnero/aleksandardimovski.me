<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Program0092Test extends TestCase
{
    use RefreshDatabase;

    public function test_list_pages_use_only_the_global_workspace_navigation(): void
    {
        $owner = $this->owner();

        foreach (['index', 'approved', 'saved', 'research', 'rejected', 'all', 'duplicates', 'expired'] as $page) {
            $route = $page === 'index' ? 'workspace.jobs.index' : 'workspace.jobs.'.$page;
            $response = $this->actingAs($owner)->get(route($route))->assertOk();
            $response->assertSee('aria-label="Workspace navigation"', false)
                ->assertDontSee('Opportunity status views')
                ->assertDontSee('status-subnav')
                ->assertDontSee('More statuses');
        }
    }

    public function test_global_header_has_primary_destinations_and_accessible_more_disclosure(): void
    {
        $response = $this->actingAs($this->owner())->get(route('workspace.jobs.index'))->assertOk();

        $response->assertSeeInOrder(['Dashboard', 'Inbox', 'Approved', 'Saved', 'Research', 'Rejected', 'All', 'Candidate profile', 'Applications', 'Follow-ups'])
            ->assertSee('data-workspace-more-trigger', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('aria-controls="workspace-more-menu"', false)
            ->assertSee(route('workspace.jobs.duplicates'), false)
            ->assertSee(route('workspace.jobs.expired'), false);
    }

    public function test_each_workspace_page_has_one_current_link_and_secondary_routes_activate_more(): void
    {
        $owner = $this->owner();
        foreach (['index', 'approved', 'saved', 'research', 'rejected', 'all', 'duplicates', 'expired'] as $page) {
            $route = $page === 'index' ? 'workspace.jobs.index' : 'workspace.jobs.'.$page;
            $html = $this->actingAs($owner)->get(route($route))->assertOk()->getContent();
            $this->assertSame(1, substr_count($this->workspaceNav($html), 'aria-current="page"'), $route);
            if (in_array($page, ['duplicates', 'expired'], true)) {
                $this->assertStringContainsString('workspace-more is-current', $html);
            }
        }
    }

    public function test_all_page_keeps_status_and_opportunity_filters(): void
    {
        $this->actingAs($this->owner())->get(route('workspace.jobs.all'))->assertOk()
            ->assertSee('id="q"', false)
            ->assertSee('id="sort"', false)
            ->assertSee('id="status"', false)
            ->assertSee('id="source"', false)
            ->assertSee('id="remote_scope"', false)
            ->assertSee('id="fit_min"', false)
            ->assertSee('id="fit_max"', false)
            ->assertSee('id="discovered_from"', false)
            ->assertSee('id="reviewed_from"', false);
    }

    public function test_navigation_behavior_supports_mobile_focus_escape_and_click_outside(): void
    {
        $javascript = file_get_contents(resource_path('js/workspace.js'));
        $css = file_get_contents(resource_path('css/workspace.css'));

        $this->assertStringContainsString("event.key === 'Escape'", $javascript);
        $this->assertStringContainsString('!more.contains(event.target)', $javascript);
        $this->assertStringContainsString('moreTrigger.focus()', $javascript);
        $this->assertStringContainsString("scrollIntoView({ block: 'nearest', inline: 'nearest' })", $javascript);
        $this->assertStringContainsString('(min-width: 621px) and (max-width: 1350px)', $javascript);
        $this->assertStringContainsString('overflow-x:auto', $css);
        $this->assertStringNotContainsString('.status-subnav', $css);
    }

    public function test_private_navigation_remains_absent_from_public_pages_and_sitemap(): void
    {
        foreach ([route('home'), route('about'), route('projects.index')] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Workspace navigation')->assertDontSee('Candidate profile');
        }

        $this->get(route('sitemap'))->assertOk()->assertDontSee('/workspace');
    }

    private function workspaceNav(string $html): string
    {
        preg_match('/<nav class="workspace-nav".*?<\/nav>/s', $html, $matches);

        return $matches[0] ?? '';
    }

    private function owner(): User
    {
        $owner = User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'email_verified_at' => now(), 'password' => Hash::make('secret-pass')]);
        $owner->is_workspace_owner = true;
        $owner->save();

        return $owner;
    }
}
