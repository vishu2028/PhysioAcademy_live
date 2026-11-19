<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\CommunityActivity;
use App\Models\Doubt;
use App\Models\DoubtSessionBooking;
use App\Models\ExamAid;
use App\Models\ExamAidMaterial;
use App\Models\FAQ;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarRequest;
use App\Models\Page;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\UnitTopic;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_finds_learning_material_by_title_and_content_and_links_to_its_topic(): void
    {
        $records = $this->createCurriculumRecords();
        $material = $records['topic']->materials()->create([
            'title' => 'Motor Learning Handout',
            'type' => 'note',
            'content' => 'Neuroplasticity principles for clinical rehabilitation.',
        ]);

        $this->get(route('search', ['query' => 'motor learning handout']))
            ->assertOk()
            ->assertSee('Motor Learning Handout')
            ->assertSee('Learning Material')
            ->assertSee(route('topics.show', ['slug' => $records['topic']->slug]) . '#learning-material-' . $material->id, false);

        $this->get(route('search', ['query' => 'LEARNING']))
            ->assertOk()
            ->assertSee('Motor Learning Handout');

        $this->get(route('search', ['query' => 'neuroplasticity principles']))
            ->assertOk()
            ->assertSee('Motor Learning Handout');

        $this->getJson('/api/v1/search?q=Motor%20Learning%20Handout')
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Motor Learning Handout',
                'category' => 'Learning Material',
            ]);
    }

    public function test_suggestions_and_search_page_cover_multiple_published_content_modules(): void
    {
        $records = $this->createCurriculumRecords();
        $faq = FAQ::create([
            'question' => 'How does vestibular adaptation work?',
            'answer' => 'Vestibular adaptation supports balance rehabilitation.',
            'category' => 'Clinical learning',
            'status' => true,
        ]);

        $aid = ExamAid::create([
            'subject_id' => $records['subject']->id,
            'unit_id' => $records['unit']->id,
            'academic_year_id' => $records['year']->id,
            'title' => 'Clinical Examination Pack',
            'description' => 'Practical clinical assessment resources.',
            'status' => true,
        ]);
        ExamAidMaterial::create([
            'exam_aid_id' => $aid->id,
            'title' => 'Vestibular Examination Checklist',
            'type' => 'pdf',
            'content' => 'Balance assessment reference material.',
        ]);

        $page = Page::create([
            'title' => 'Vestibular Guide',
            'slug' => 'vestibular-guide-search-test',
            'content' => 'Published guide to vestibular rehabilitation.',
            'status' => true,
            'is_protected' => false,
        ]);
        $webinar = Webinar::create([
            'title' => 'Vestibular Rehabilitation Webinar',
            'slug' => 'vestibular-rehabilitation-search-test',
            'description' => 'A published webinar on balance assessment.',
            'starts_at' => now()->addWeek(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('search.suggestions', ['query' => 'vestibular']))
            ->assertOk()
            ->assertJsonFragment(['title' => 'How does vestibular adaptation work?'])
            ->assertJsonFragment(['title' => 'Clinical Examination Pack'])
            ->assertJsonFragment(['title' => 'Vestibular Guide'])
            ->assertJsonFragment(['title' => 'Vestibular Rehabilitation Webinar']);

        $this->get(route('search', ['query' => 'vestibular']))
            ->assertOk()
            ->assertSee('FAQ')
            ->assertSee('Exam Aid')
            ->assertSee('Published Page')
            ->assertSee('Webinar');

        $this->get(route('search', ['query' => 'How does vestibular adaptation work']))
            ->assertOk()
            ->assertSee(route('faq') . '#faq-' . $faq->id, false);
        $this->get(route('search', ['query' => 'Vestibular Guide']))
            ->assertOk()
            ->assertSee(route('page.show', ['slug' => $page->slug]), false);
        $this->get(route('search', ['query' => 'Vestibular Rehabilitation Webinar']))
            ->assertOk()
            ->assertSee(route('webinars.show', ['webinar' => $webinar->slug]), false);
        $examAidResponse = $this->get(route('search', ['query' => 'Vestibular Examination Checklist']))
            ->assertOk()
            ->assertSee('Exam Aid');
        $this->assertStringContainsString(
            route('exam-aid', [
                'q' => $aid->title,
                'exam_aid' => $aid->id,
            ]) . '#exam-aid-card-' . $aid->id,
            html_entity_decode($examAidResponse->getContent(), ENT_QUOTES)
        );
    }

    public function test_subject_academic_year_unit_and_topic_relationships_are_searchable(): void
    {
        $records = $this->createCurriculumRecords();

        $this->get(route('search', ['query' => 'Clinical Physiology']))
            ->assertOk()
            ->assertSee('Subject')
            ->assertSee(route('topics.index') . '#subject-' . $records['subject']->id, false);
        $this->get(route('search', ['query' => 'Year 2']))
            ->assertOk()
            ->assertSee('Academic Year')
            ->assertSee(route('topics.year', ['year' => $records['year']->slug]), false);
        $this->get(route('search', ['query' => 'Cardiology']))
            ->assertOk()
            ->assertSee('Unit')
            ->assertSee(route('topics.index') . '#unit-panel-' . $records['subject']->id . '-' . $records['unit']->id, false);
        $this->get(route('search', ['query' => 'Cardiac Rehabilitation']))
            ->assertOk()
            ->assertSee('Unit Topic')
            ->assertSee('Cardiac Rehabilitation Basics')
            ->assertSee('Cardiology')
            ->assertSee(route('topics.show', ['slug' => $records['topic']->slug]), false);
    }

    public function test_category_filters_paginate_complete_case_insensitive_result_sets_without_counts(): void
    {
        $records = $this->createCurriculumRecords();
        $topics = collect(range(1, 23))->map(fn (int $number) => Topic::create([
            'title' => "Matchable Clinical Topic {$number}",
            'slug' => "matchable-clinical-topic-{$number}",
            'description' => 'Newly created searchable curriculum content.',
            'subject_id' => $records['subject']->id,
            'academic_year_id' => $records['year']->id,
            'unit_topic_id' => $records['unitTopic']->id,
            'status' => true,
        ]));
        $material = $topics->first()->materials()->create([
            'title' => 'Matchable Learning Material',
            'type' => 'note',
            'content' => 'Matchable material content.',
        ]);
        $aid = ExamAid::create([
            'subject_id' => $records['subject']->id,
            'unit_id' => $records['unit']->id,
            'academic_year_id' => $records['year']->id,
            'title' => 'Matchable Exam Aid',
            'description' => 'Searchable exam resource.',
            'status' => true,
        ]);
        $faq = FAQ::create([
            'question' => 'Matchable FAQ',
            'answer' => 'Searchable frequently asked question.',
            'status' => true,
        ]);

        $all = $this->get(route('search', ['query' => 'MATCHABLE', 'filter' => 'all']))
            ->assertOk()
            ->assertSee('Matchable Clinical Topic 1')
            ->assertSee('Learning Material')
            ->assertSee('Exam Aid')
            ->assertSee('FAQs')
            ->assertDontSee('resultCountText');

        $this->assertSame(26, app(\App\Services\SiteSearch::class)->search('MATCHABLE')->count());
        $allContent = html_entity_decode($all->getContent(), ENT_QUOTES);
        $this->assertStringContainsString('page=2', $allContent);
        $this->assertStringContainsString('site-search-pagination-nav', $allContent);
        $this->assertStringContainsString('rel="next"', $allContent);
        $this->assertStringContainsString('width="18" height="18"', $allContent);
        $this->assertStringNotContainsString('w-5 h-5', $allContent);
        $this->get(route('search', ['query' => 'MATCHABLE', 'filter' => 'all', 'page' => 2]))
            ->assertOk()
            ->assertSee('Matchable Learning Material')
            ->assertSee('Matchable Exam Aid')
            ->assertSee('Matchable FAQ');

        $materialResponse = $this->get(route('search', [
            'query' => 'matchable',
            'filter' => 'learning-material',
        ]))->assertOk();
        $this->assertStringContainsString(
            route('topics.show', ['slug' => $topics->first()->slug]) . '#learning-material-' . $material->id,
            html_entity_decode($materialResponse->getContent(), ENT_QUOTES),
        );

        $examAidResponse = $this->get(route('search', [
            'query' => 'matchable',
            'filter' => 'exam-aid',
        ]))->assertOk();
        $this->assertStringContainsString(
            route('exam-aid', ['q' => $aid->title, 'exam_aid' => $aid->id]) . '#exam-aid-card-' . $aid->id,
            html_entity_decode($examAidResponse->getContent(), ENT_QUOTES),
        );

        $this->get(route('search', ['query' => 'matchable', 'filter' => 'topic']))
            ->assertOk()
            ->assertSee('Matchable Clinical Topic 23')
            ->assertDontSee('Matchable Learning Material');

        $this->get(route('search', ['query' => 'matchable', 'filter' => 'topic', 'page' => 2]))
            ->assertOk()
            ->assertSee('Matchable Clinical Topic 9')
            ->assertDontSee('Matchable Learning Material');

        $this->get(route('search', ['query' => 'matchable', 'filter' => 'learning-material']))
            ->assertOk()
            ->assertSee('Matchable Learning Material')
            ->assertDontSee('Matchable Exam Aid');

        $this->get(route('search', ['query' => 'matchable', 'filter' => 'exam-aid']))
            ->assertOk()
            ->assertSee('Matchable Exam Aid')
            ->assertDontSee('Matchable FAQ');

        $faqResponse = $this->get(route('search', ['query' => 'matchable', 'filter' => 'faq']))
            ->assertOk()
            ->assertSee('Matchable FAQ')
            ->assertDontSee('Matchable Clinical Topic');
        $this->assertStringContainsString(
            route('faq') . '#faq-' . $faq->id,
            html_entity_decode($faqResponse->getContent(), ENT_QUOTES),
        );
    }

    public function test_search_pagination_stays_compact_with_large_page_ranges_and_working_arrows(): void
    {
        $paginator = new LengthAwarePaginator(
            collect(),
            2000,
            20,
            50,
            ['path' => route('search'), 'query' => ['query' => 'topic']],
        );
        $html = $paginator->links('vendor.pagination.search')->render();

        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
        $this->assertStringContainsString('aria-label="Go to page 1"', $html);
        $this->assertStringContainsString('aria-label="Go to page 49"', $html);
        $this->assertStringContainsString('aria-current="page">50</span>', $html);
        $this->assertStringContainsString('aria-label="Go to page 51"', $html);
        $this->assertStringContainsString('aria-label="Go to page 100"', $html);
        $this->assertStringContainsString('<span class="site-search-page-ellipsis"', $html);
        $this->assertSame(2, substr_count($html, 'width="18" height="18"'));
        $this->assertStringNotContainsString('w-5 h-5', $html);
    }

    public function test_trending_topics_are_separate_from_search_results(): void
    {
        $records = $this->createCurriculumRecords();
        $records['topic']->update([
            'title' => 'Searchable Trending Topic',
            'slug' => 'searchable-trending-topic',
            'is_trending' => true,
        ]);

        $results = app(\App\Services\SiteSearch::class)->search('Searchable Trending');

        $this->assertCount(1, $results);
        $this->assertSame('Topic', $results->sole()['category']);

        $this->get(route('search', ['query' => 'Searchable Trending']))
            ->assertOk()
            ->assertSee('Topics')
            ->assertSee('Trending Topics')
            ->assertSee('Searchable Trending Topic');
    }

    public function test_initial_search_state_shows_instructions_without_empty_message_or_counts(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('Enter a search term to find content')
            ->assertSee('Enter a topic, learning resource, subject, unit, Exam Aid, or FAQ above.')
            ->assertDontSee('No matching results found')
            ->assertDontSee('resultCountText')
            ->assertDontSee('All (')
            ->assertDontSee('Topics (')
            ->assertDontSee('Learning Material (')
            ->assertDontSee('Exam Aid (')
            ->assertDontSee('FAQs (');
    }

    public function test_category_filters_browse_existing_records_without_a_search_term(): void
    {
        $records = $this->createCurriculumRecords();
        $records['topic']->materials()->create([
            'title' => 'Browseable Learning Material',
            'type' => 'note',
            'content' => 'Published learning material for browse testing.',
        ]);
        ExamAid::create([
            'subject_id' => $records['subject']->id,
            'unit_id' => $records['unit']->id,
            'academic_year_id' => $records['year']->id,
            'title' => 'Browseable Exam Aid',
            'description' => 'Published exam aid for browse testing.',
            'status' => true,
        ]);
        FAQ::create([
            'question' => 'Browseable FAQ',
            'answer' => 'Published FAQ for browse testing.',
            'status' => true,
        ]);

        $this->get(route('search', ['filter' => 'all']))
            ->assertOk()
            ->assertSee($records['topic']->title)
            ->assertSee('Browseable Learning Material')
            ->assertSee('Browseable Exam Aid')
            ->assertSee('Browseable FAQ');

        $this->get(route('search', ['filter' => 'topic']))
            ->assertOk()
            ->assertSee($records['topic']->title)
            ->assertDontSee('Browseable Learning Material')
            ->assertDontSee('Browseable Exam Aid')
            ->assertDontSee('Browseable FAQ');

        $this->get(route('search', ['filter' => 'learning-material']))
            ->assertOk()
            ->assertSee('Browseable Learning Material')
            ->assertDontSee($records['topic']->title)
            ->assertDontSee('Browseable Exam Aid');

        $this->get(route('search', ['filter' => 'exam-aid']))
            ->assertOk()
            ->assertSee('Browseable Exam Aid')
            ->assertDontSee('Browseable Learning Material')
            ->assertDontSee('Browseable FAQ');

        $this->get(route('search', ['filter' => 'faq']))
            ->assertOk()
            ->assertSee('Browseable FAQ')
            ->assertDontSee('Browseable Exam Aid')
            ->assertDontSee($records['topic']->title);
    }

    public function test_newly_added_and_updated_content_is_searchable_and_unpublished_records_are_hidden(): void
    {
        $records = $this->createCurriculumRecords();
        $material = $records['topic']->materials()->create([
            'title' => 'Recently Added Resource',
            'type' => 'note',
            'content' => 'Updated clinical reference details.',
        ]);

        $this->get(route('search', ['query' => 'Recently Added Resource']))
            ->assertOk()
            ->assertSee($material->title);

        $material->update([
            'title' => 'Updated Clinical Resource',
            'content' => 'Freshly updated content for dynamic search.',
        ]);

        $this->get(route('search', ['query' => 'Recently Added Resource']))
            ->assertOk()
            ->assertDontSee('Updated Clinical Resource');
        $this->get(route('search', ['query' => 'Freshly updated content']))
            ->assertOk()
            ->assertSee('Updated Clinical Resource');

        $records['topic']->update(['status' => false]);
        $this->get(route('search', ['query' => 'Freshly updated content']))
            ->assertOk()
            ->assertSee('No matching results found')
            ->assertDontSee('Updated Clinical Resource');

        FAQ::create([
            'question' => 'Unpublished private FAQ term',
            'answer' => 'This should not be indexed.',
            'status' => false,
        ]);
        Webinar::create([
            'title' => 'Unpublished Session term',
            'slug' => 'unpublished-session-search-test',
            'description' => 'Draft event content',
            'starts_at' => now()->addWeek(),
            'status' => 'draft',
        ]);

        $this->get(route('search', ['query' => 'Unpublished']))
            ->assertOk()
            ->assertSee('No matching results found');
    }

    public function test_public_homepage_announcements_and_activities_are_searchable(): void
    {
        $announcement = Announcement::create([
            'title' => 'New Balance Assessment Workshop',
            'date' => now()->toDateString(),
        ]);
        $activity = CommunityActivity::create([
            'title' => 'Neuro Rehab Study Circle',
            'subject' => 'Neurology',
            'time' => 'Today',
        ]);

        $this->get(route('search', ['query' => 'balance assessment workshop']))
            ->assertOk()
            ->assertSee('Announcement')
            ->assertSee(route('home') . '#announcement-' . $announcement->id, false);

        $this->get(route('search', ['query' => 'neuro rehab study circle']))
            ->assertOk()
            ->assertSee('Community Activity')
            ->assertSee(route('home') . '#community-activity-' . $activity->id, false);
    }

    public function test_restricted_exam_aid_details_and_full_topic_descriptions_are_not_exposed_to_guests(): void
    {
        $records = $this->createCurriculumRecords();
        $records['topic']->update([
            'is_protected' => true,
            'description' => 'Preview text that is visible. SECRET-PROTECTED-TOPIC-CONTENT.',
        ]);
        $aid = ExamAid::create([
            'subject_id' => $records['subject']->id,
            'title' => 'Public Resource Card',
            'description' => 'Public card description.',
            'viva_question' => 'SECRET-EXAM-QUESTION',
            'status' => true,
        ]);
        $aid->materials()->create([
            'title' => 'SECRET-EXAM-MATERIAL',
            'type' => 'pdf',
            'content' => 'SECRET-EXAM-MATERIAL-CONTENT',
        ]);

        $this->get(route('search', ['query' => 'SECRET-EXAM-QUESTION']))
            ->assertOk()
            ->assertSee('No matching results found')
            ->assertDontSee('SECRET-EXAM-QUESTION</h2>');
        $this->get(route('search', ['query' => 'SECRET-EXAM-MATERIAL']))
            ->assertOk()
            ->assertSee('No matching results found');

        $this->get(route('search', ['query' => $records['topic']->title]))
            ->assertOk()
            ->assertSee($records['topic']->title)
            ->assertDontSee('SECRET-PROTECTED-TOPIC-CONTENT');

        $this->actingAs(User::factory()->create())
            ->get(route('search', ['query' => 'SECRET-EXAM-QUESTION']))
            ->assertOk()
            ->assertSee($aid->title);
    }

    public function test_student_doubts_are_searchable_only_by_their_owner_and_empty_state_is_useful(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        Doubt::create([
            'user_id' => $owner->id,
            'topic' => 'Owner-only ankle mechanics',
            'message' => 'Private note about ankle mechanics.',
            'status' => 'pending',
        ]);
        Doubt::create([
            'user_id' => $otherUser->id,
            'topic' => 'Other-student private shoulder case',
            'message' => 'This must not appear in global search.',
            'status' => 'pending',
        ]);
        DoubtSessionBooking::create([
            'booking_reference' => 'DS-GLOBAL-OWNER',
            'user_id' => $owner->id,
            'student_name' => $owner->name,
            'student_email' => $owner->email,
            'phone' => '9000000000',
            'topic' => 'Owner-only doubt session',
            'doubt' => 'Private information for my session.',
        ]);
        DoubtSessionBooking::create([
            'booking_reference' => 'DS-GLOBAL-OTHER',
            'user_id' => $otherUser->id,
            'student_name' => $otherUser->name,
            'student_email' => $otherUser->email,
            'phone' => '9000000001',
            'topic' => 'Other-student private session',
            'doubt' => 'Not visible to the current user.',
        ]);
        $groupWebinar = GroupWebinarRequest::create([
            'organizer_id' => $owner->id,
            'reference' => 'GW-GLOBAL-OWNER',
            'organizer_name' => $owner->name,
            'organizer_email' => $owner->email,
            'topic' => 'Owner-only group webinar',
            'description' => 'Private group learning session.',
            'participant_count' => 2,
            'minimum_participants' => 2,
            'maximum_participants' => 10,
            'duration_minutes' => 60,
            'status' => GroupWebinarRequest::STATUS_CONFIRMED,
            'scheduled_at' => now()->addDay(),
            'meeting_link' => 'https://meet.example.test/private-session',
        ]);
        $groupWebinar->participants()->create([
            'user_id' => $owner->id,
            'name' => $owner->name,
            'email' => $owner->email,
            'email_normalized' => mb_strtolower($owner->email),
            'payment_status' => GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
        ]);

        $this->get(route('search', ['query' => 'ankle mechanics']))
            ->assertOk()
            ->assertDontSee('Owner-only ankle mechanics');

        $this->actingAs($owner)
            ->get(route('search', ['query' => 'ankle mechanics']))
            ->assertOk()
            ->assertSee('Owner-only ankle mechanics')
            ->assertSee('Student Doubt')
            ->assertDontSee('Other-student private shoulder case');
        $this->get(route('search', ['query' => 'Owner-only doubt session']))
            ->assertOk()
            ->assertSee('Doubt Session')
            ->assertSee(route('doubt-sessions.history'), false);
        $this->get(route('search', ['query' => 'Owner-only group webinar']))
            ->assertOk()
            ->assertSee('Group Webinar')
            ->assertSee(route('group-webinars.join', ['groupWebinar' => $groupWebinar->id]), false);
        $this->get(route('search', ['query' => 'Other-student private session']))
            ->assertOk()
            ->assertSee('No matching results found')
            ->assertDontSee('Other-student private session</h2>');

        $this->get(route('search', ['query' => 'nothing-matches-this']))
            ->assertOk()
            ->assertSee('No matching results found')
            ->assertSee('Try a topic, learning resource');
    }

    private function createCurriculumRecords(): array
    {
        $year = AcademicYear::create([
            'name' => 'Year 2',
            'slug' => 'year-2-global-search',
            'status' => true,
        ]);
        $subject = Subject::create([
            'name' => 'Clinical Physiology',
            'slug' => 'clinical-physiology-global-search',
            'status' => true,
        ]);
        $unit = Unit::create([
            'subject_id' => $subject->id,
            'name' => 'Cardiology',
            'slug' => 'cardiology-global-search',
            'is_active' => true,
        ]);
        $unitTopic = UnitTopic::create([
            'subject_id' => $subject->id,
            'unit_id' => $unit->id,
            'title' => 'Cardiac Rehabilitation',
            'slug' => 'cardiac-rehabilitation-global-search',
            'status' => true,
        ]);
        $topic = Topic::create([
            'title' => 'Cardiac Rehabilitation Basics',
            'slug' => 'cardiac-rehabilitation-basics-global-search',
            'description' => 'Core rehabilitation principles for clinical practice.',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
            'status' => true,
        ]);

        return compact('year', 'subject', 'unit', 'unitTopic', 'topic');
    }
}
