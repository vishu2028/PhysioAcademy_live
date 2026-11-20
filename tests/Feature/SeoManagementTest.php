<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ExamAid;
use App\Models\ExamAidCategory;
use App\Models\ExamAidMaterial;
use App\Models\LearningMaterial;
use App\Models\Page;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\UnitTopic;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_content_forms_no_longer_expose_seo_controls(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'admin');

        foreach ([
            'admin.subjects.create',
            'admin.academic-years.create',
            'admin.units.create',
            'admin.pages.create',
            'admin.topics.create',
            'admin.exam-aid-categories.create',
            'admin.exam-aids.create',
            'admin.webinars.create',
            'admin.settings.index',
        ] as $routeName) {
            $response = $this->get(route($routeName))->assertOk();
            $response->assertDontSee('SEO Metadata')
                ->assertDontSee('Google search preview')
                ->assertDontSee('SEO & Meta')
                ->assertDontSee('Optional material SEO')
                ->assertDontSee('name="seo_title"', false)
                ->assertDontSee('name="meta_description"', false)
                ->assertDontSee('name="canonical_url"', false);
        }

        $this->post(route('admin.subjects.store'), [
            'name' => 'Automatic SEO Subject',
            'description' => 'A subject summary generated from its content.',
            'status' => '1',
            'seo_title' => 'Manually supplied title',
            'seo_slug' => 'manual-title',
            'canonical_url' => 'https://example.test/manual',
            'robots_index' => '0',
        ])->assertRedirect(route('admin.subjects.index'));

        $subject = Subject::where('name', 'Automatic SEO Subject')->firstOrFail();
        $this->assertSame('Automatic SEO Subject', $subject->seo_title);
        $this->assertSame('A subject summary generated from its content.', $subject->meta_description);
        $this->assertSame('automatic-seo-subject', $subject->seo_slug);
        $this->assertNull($subject->canonical_url);
    }

    public function test_seo_metadata_is_generated_and_refreshed_for_supported_content(): void
    {
        [$subject, $year, $unit, $unitTopic] = $this->createCurriculum();
        $category = ExamAidCategory::create([
            'name' => 'Past Papers',
            'slug' => 'past-papers',
            'description' => 'Practice questions from prior examinations.',
            'is_active' => true,
        ]);
        $topic = Topic::create([
            'title' => 'Cardiopulmonary Assessment',
            'slug' => 'cardiopulmonary-assessment',
            'description' => '<p>A practical cardiopulmonary assessment guide.</p>',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
        ]);
        $learningMaterial = LearningMaterial::create([
            'topic_id' => $topic->id,
            'title' => 'Assessment Checklist',
            'type' => 'note',
            'content' => 'A clinical checklist for student practice.',
        ]);
        $examAid = ExamAid::create([
            'subject_id' => $subject->id,
            'title' => 'Clinical Examination',
            'description' => 'Clinical examination preparation material.',
        ]);
        $examAidMaterial = ExamAidMaterial::create([
            'exam_aid_id' => $examAid->id,
            'title' => 'Examination Notes',
            'type' => 'note',
            'content' => 'Notes for preparing for the examination.',
        ]);
        $page = Page::create([
            'title' => 'Public Curriculum Guide',
            'slug' => 'public-curriculum-guide',
            'content' => '<p>A public guide for physiotherapy students.</p>',
            'status' => true,
        ]);
        $webinar = Webinar::create([
            'title' => 'Physiotherapy Assessment Webinar',
            'slug' => 'physiotherapy-assessment-webinar',
            'description' => 'An online session about clinical assessment.',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'published',
        ]);

        foreach ([$subject, $year, $unit, $category, $topic, $learningMaterial, $examAid, $examAidMaterial, $page, $webinar] as $content) {
            $this->assertSame(
                $content->title ?: $content->name,
                $content->seo_title,
                class_basename($content) . ' SEO title'
            );
            $this->assertSame($content->seo_title, $content->og_title);
            if (! $content instanceof Page) {
                $this->assertNotEmpty($content->meta_description, class_basename($content) . ' description');
                $this->assertSame($content->meta_description, $content->og_description);
            }
        }

        $this->assertSame($topic->slug, $topic->seo_slug);
        $this->assertSame($page->title, $page->meta_title);

        $seoMetadata = app(\App\Services\SeoMetadata::class);
        foreach ([
            $topic->fresh(),
            $learningMaterial->fresh(),
            $subject->fresh(),
            $unit->fresh(),
            $examAid->fresh(),
        ] as $indexableContent) {
            $this->assertSame(
                'index, follow',
                $seoMetadata->for($indexableContent, $indexableContent->title ?: $indexableContent->name)['robots'],
                class_basename($indexableContent) . ' robots directives'
            );
        }

        $topic->description = '<p>' . str_repeat('Evidence-based physiotherapy guidance ', 8) . '</p>';
        $longDescription = $seoMetadata->for($topic, $topic->title)['description'];
        $this->assertLessThanOrEqual(160, mb_strlen($longDescription));
        $this->assertStringNotContainsString('<', $longDescription);
        $this->assertStringEndsWith('...', $longDescription);

        $topic->update([
            'title' => 'Updated Cardiopulmonary Assessment',
            'description' => 'Updated assessment guidance for physiotherapy students.',
        ]);
        $learningMaterial->update(['content' => 'Updated clinical checklist content.']);

        $this->assertSame('Updated Cardiopulmonary Assessment', $topic->fresh()->seo_title);
        $this->assertSame('Updated assessment guidance for physiotherapy students.', $topic->fresh()->meta_description);
        $this->assertSame('Updated clinical checklist content.', $learningMaterial->fresh()->meta_description);

        $page->update(['content' => '<p>Updated public content with an actual image.</p><img src="/storage/guide.jpg">']);
        $this->get(route('page.show', ['slug' => $page->slug]))
            ->assertOk()
            ->assertSee('<title>Public Curriculum Guide —', false)
            ->assertSee('<meta name="description" content="Updated public content with an actual image.">', false)
            ->assertSee('<link rel="canonical" href="' . route('page.show', ['slug' => $page->slug]) . '">', false)
            ->assertSee('<meta property="og:image" content="' . rtrim(config('app.url'), '/') . '/storage/guide.jpg">', false);
    }

    public function test_metadata_uses_existing_public_urls_and_sitemap_only_lists_eligible_content(): void
    {
        Role::findOrCreate('user', 'web');
        [$subject, $year, $unit, $unitTopic] = $this->createCurriculum();
        $publicTopic = Topic::create([
            'title' => 'Public Respiratory Topic',
            'slug' => 'public-respiratory-topic',
            'description' => 'Respiratory assessment and treatment basics.',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
        ]);
        $secondPublicTopic = Topic::create([
            'title' => 'Public Motor Learning Topic',
            'slug' => 'public-motor-learning-topic',
            'description' => 'Motor learning principles for physiotherapy.',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
        ]);
        $privateTopic = Topic::create([
            'title' => 'Protected Topic',
            'slug' => 'protected-topic',
            'description' => 'Private learning content.',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
            'is_protected' => true,
        ]);
        $noindexTopic = Topic::create([
            'title' => 'Noindex Topic',
            'slug' => 'noindex-topic',
            'description' => 'Do not include this content in search.',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
            'robots_index' => false,
        ]);
        $unpublishedTopic = Topic::create([
            'title' => 'Unpublished Topic',
            'slug' => 'unpublished-topic',
            'description' => 'This topic is not published.',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
            'status' => false,
        ]);

        Page::create([
            'title' => 'Published Curriculum Guide',
            'slug' => 'published-curriculum-guide',
            'content' => 'This is a public CMS page.',
            'status' => true,
        ]);
        Page::create([
            'title' => 'Draft Guide',
            'slug' => 'draft-guide',
            'content' => 'This page is unpublished.',
            'status' => false,
        ]);

        ExamAidCategory::create([
            'name' => 'Past Papers',
            'slug' => 'past-papers',
            'is_active' => true,
        ]);
        ExamAidCategory::create([
            'name' => 'Private Category',
            'slug' => 'private-category',
            'is_active' => false,
        ]);

        Webinar::create([
            'title' => 'Published Webinar',
            'slug' => 'published-webinar',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'published',
        ]);
        Webinar::create([
            'title' => 'Draft Webinar',
            'slug' => 'draft-webinar',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'draft',
        ]);

        $this->get(route('topics.show', ['slug' => $publicTopic->slug]))
            ->assertOk()
            ->assertSee('<title>Public Respiratory Topic —', false)
            ->assertSee('<meta name="description" content="Respiratory assessment and treatment basics.">', false)
            ->assertSee('<link rel="canonical" href="' . route('topics.show', ['slug' => $publicTopic->slug]) . '">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false);
        $this->get(route('topics.show', ['slug' => $secondPublicTopic->slug]))
            ->assertOk()
            ->assertSee('<title>Public Motor Learning Topic —', false)
            ->assertSee('<link rel="canonical" href="' . route('topics.show', ['slug' => $secondPublicTopic->slug]) . '">', false);
        $this->get(route('search', ['query' => 'Public Motor Learning', 'filter' => 'topic']))
            ->assertOk()
            ->assertSee('Public Motor Learning Topic')
            ->assertSee(route('topics.show', ['slug' => $secondPublicTopic->slug]), false);
        $this->get(route('topics.year', ['year' => $year->slug]))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . route('topics.year', ['year' => $year->slug]) . '">', false);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description"', false);
        $this->get(route('topics.index'))
            ->assertOk()
            ->assertSee('<title>Physiotherapy Topics and Study Guides —', false);
        $this->get(route('topics.year'))
            ->assertOk()
            ->assertSee('<title>Syllabus by Academic Year —', false);
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('<title>FAQ —', false);
        $this->get(route('webinars.index'))
            ->assertOk()
            ->assertSee('<title>Physiotherapy Webinars —', false);
        $this->get(route('exam-aid.category', ['slug' => 'past-papers']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . route('exam-aid.category', ['slug' => 'past-papers']) . '">', false);
        $this->get(route('webinars.show', ['webinar' => 'published-webinar']))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . route('webinars.show', ['webinar' => 'published-webinar']) . '">', false);
        $this->get(route('topics.show', ['slug' => $privateTopic->slug]))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $this->get(route('topics.show', ['slug' => $unpublishedTopic->slug]))
            ->assertNotFound();

        $localAppUrl = config('app.url');
        config()->set('app.url', 'https://physiosource.in');
        $productionMetadataResponse = $this->get('/topic/' . $publicTopic->slug)->assertOk();
        $productionCanonical = 'https://physiosource.in/topic/' . $publicTopic->slug;
        $productionMetadataResponse
            ->assertSee('<link rel="canonical" href="' . $productionCanonical . '">', false)
            ->assertSee('<meta property="og:url" content="' . $productionCanonical . '">', false);
        preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $productionMetadataResponse->getContent(),
            $structuredDataMatch
        );
        $this->assertSame(
            $productionCanonical,
            json_decode($structuredDataMatch[1] ?? '{}', true)['url'] ?? null
        );
        config()->set('app.url', $localAppUrl);

        $sitemap = $this->get(route('sitemap'))->assertOk();
        $sitemap->assertSee(route('topics.show', ['slug' => $publicTopic->slug]), false)
            ->assertSee(route('page.show', ['slug' => 'published-curriculum-guide']), false)
            ->assertSee(route('exam-aid.category', ['slug' => 'past-papers']), false)
            ->assertSee(route('webinars.show', ['webinar' => 'published-webinar']), false)
            ->assertDontSee(route('topics.show', ['slug' => $privateTopic->slug]), false)
            ->assertDontSee(route('topics.show', ['slug' => $noindexTopic->slug]), false)
            ->assertDontSee(route('page.show', ['slug' => 'draft-guide']), false)
            ->assertDontSee(route('exam-aid.category', ['slug' => 'private-category']), false)
            ->assertDontSee(route('webinars.show', ['webinar' => 'draft-webinar']), false);

        $this->assertSame(
            'noindex, nofollow',
            app(\App\Services\SeoMetadata::class)->for($unpublishedTopic, $unpublishedTopic->title)['robots']
        );
    }

    private function createCurriculum(): array
    {
        $subject = Subject::create([
            'name' => 'Physiotherapy',
            'slug' => 'physiotherapy',
            'description' => 'Core physiotherapy concepts.',
            'status' => true,
        ]);
        $year = AcademicYear::create([
            'name' => 'First Year',
            'slug' => 'first-year',
            'description' => 'First year curriculum.',
            'status' => true,
        ]);
        $unit = Unit::create([
            'subject_id' => $subject->id,
            'name' => 'Clinical Foundations',
            'slug' => 'clinical-foundations',
            'description' => 'Clinical foundation lessons.',
            'is_active' => true,
        ]);
        $unitTopic = UnitTopic::create([
            'subject_id' => $subject->id,
            'unit_id' => $unit->id,
            'title' => 'Assessment',
            'slug' => 'assessment',
            'status' => true,
        ]);

        return [$subject, $year, $unit, $unitTopic];
    }
}
