<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\ExamAidBanner;
use App\Models\Page;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SharedNavbarDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_topics_dropdown_is_closed_on_normal_and_topics_pages(): void
    {
        Role::findOrCreate('user', 'web');
        $user = User::factory()->create();
        ExamAidBanner::create(['main_title' => 'Exam Aid']);
        foreach ([
            ['title' => 'About Us', 'slug' => 'about-us'],
            ['title' => 'Exam Aid', 'slug' => 'exam-aid'],
        ] as $page) {
            Page::create([
                ...$page,
                'content' => 'Published page content.',
                'status' => true,
            ]);
        }
        $year = AcademicYear::create([
            'name' => 'Year 1',
            'slug' => 'navbar-year-one',
            'status' => true,
        ]);
        $subject = Subject::create([
            'name' => 'Navbar Subject',
            'slug' => 'navbar-subject',
            'status' => true,
        ]);
        $topic = Topic::create([
            'title' => 'Navbar Topic',
            'slug' => 'navbar-topic',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'status' => true,
        ]);

        $pages = [
            route('home') => false,
            route('about') => false,
            route('exam-aid') => false,
            route('faq') => false,
            route('support.feedback') => false,
            route('search') => true,
            route('topics.show', ['slug' => $topic->slug]) => true,
            route('topics.year') => true,
            route('topics.index') => true,
        ];

        foreach ($pages as $url => $topicsRouteActive) {
            $response = $this->actingAs($user)->get($url);
            $this->assertSame(200, $response->status(), $url);
            $html = $response->getContent();

            $this->assertMatchesRegularExpression(
                '/<div class="nav-dropdown topics-nav-dropdown([^"]*)">/',
                $html
            );
            preg_match(
                '/<div class="nav-dropdown topics-nav-dropdown([^"]*)">/',
                $html,
                $topicsDropdown
            );

            $this->assertArrayHasKey(1, $topicsDropdown);
            $this->assertStringNotContainsString('is-open', $topicsDropdown[1], $url);
            $this->assertSame(
                $topicsRouteActive,
                str_contains($topicsDropdown[1], 'route-active'),
                $url
            );
            $response
                ->assertSee('aria-expanded="false"', false)
                ->assertSee(route('search'), false)
                ->assertSee(route('topics.year'), false)
                ->assertSee(route('topics.index'), false);
        }
    }

    public function test_shared_header_dropdowns_are_closed_until_explicitly_clicked(): void
    {
        Role::findOrCreate('user', 'web');
        $user = User::factory()->create();

        foreach ([route('home'), route('doubts.mine')] as $url) {
            $response = $this->actingAs($user)->get($url)->assertOk();
            $html = $response->getContent();

            $headEnd = strpos($html, '</head>');
            $bodyStart = strpos($html, '<body');

            $this->assertNotFalse($headEnd);
            $this->assertNotFalse($bodyStart);
            $this->assertLessThan($bodyStart, $headEnd);
            $this->assertStringContainsString(
                '[data-notification-menu] > [data-notification-dropdown]',
                substr($html, 0, $headEnd)
            );
            $this->assertStringContainsString(
                '.nav-user-menu > .nav-user-dropdown',
                substr($html, 0, $headEnd)
            );
            $this->assertStringContainsString('flex: 0 0 54px;', $html);
            $this->assertStringContainsString('flex-basis: 46px;', $html);
            $this->assertStringContainsString('object-fit: cover;', $html);
            $this->assertStringContainsString('object-position: center;', $html);
            $this->assertStringContainsString(
                'transition: box-shadow 0.22s ease, border-color 0.22s ease;',
                $html
            );
            $this->assertMatchesRegularExpression(
                '/\\.nav-user-avatar:hover\\s*\\{[^}]*box-shadow:[^}]*\\}/s',
                $html
            );
            preg_match('/\\.nav-user-avatar:hover\\s*\\{([^}]*)\\}/s', $html, $avatarHoverStyles);
            $this->assertArrayHasKey(1, $avatarHoverStyles);
            $this->assertStringNotContainsString('transform:', $avatarHoverStyles[1]);
            $this->assertStringNotContainsString('animation:', $avatarHoverStyles[1]);

            $response
                ->assertSee('aria-label="Open notifications"', false)
                ->assertSee('aria-controls="student-notification-dropdown"', false)
                ->assertSee('aria-expanded="false"', false)
                ->assertSee('id="student-notification-dropdown"', false)
                ->assertSee('aria-hidden="true"', false)
                ->assertSee('aria-label="Open profile menu"', false)
                ->assertSee('id="nav-user-dropdown"', false)
                ->assertSee('const openMenuSelector', false)
                ->assertSee('event.key !== \'Escape\'', false);
        }

        $this->assertStringNotContainsString(
            '.nav-user-menu:hover .nav-user-dropdown',
            $html
        );
        $this->assertStringNotContainsString(
            '.nav-user-menu:focus-within .nav-user-dropdown',
            $html
        );
    }
}
