<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ExamAid;
use App\Models\ExamAidCategory;
use App\Models\ExamAidMaterial;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\UnitTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAidSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_partial_case_insensitive_material_subject_and_topic_matches(): void
    {
        $records = $this->createSearchRecords();

        foreach ([
            'wAvEfOrM',
            'ventricular tracing',
            'clinical physiology',
            'cardiac action potential',
            'cardiac rhythm notes',
            'cardiac resource category',
        ] as $term) {
            $this->getJson(route('exam-aid.search', ['q' => $term]))
                ->assertOk()
                ->assertJsonFragment([
                    'id' => $records['aid']->id,
                    'title' => 'Cardiac Resource Bundle',
                ]);
        }
    }

    public function test_search_form_filters_actual_results_and_preserves_year_and_type_filters(): void
    {
        $records = $this->createSearchRecords();

        $response = $this->get(route('exam-aid', [
            'q' => 'wAvEfOrM',
            'year' => $records['year']->id,
            'resource_type' => 'pdf',
        ]))
            ->assertOk()
            ->assertSee('Cardiac Resource Bundle')
            ->assertDontSee('Respiratory Resource Bundle')
            ->assertSee('name="q"', false)
            ->assertSee('value="wAvEfOrM"', false)
            ->assertSee('name="year"', false)
            ->assertSee('name="resource_type"', false)
            ->assertSee('type="submit" class="exam-search-submit"', false)
            ->assertSee('Clear search')
            ->assertSee('Search');

        $this->assertStringContainsString(
            'href="'.route('exam-aid', [
                'year' => $records['year']->id,
                'resource_type' => 'pdf',
            ]).'"',
            html_entity_decode($response->getContent(), ENT_QUOTES)
        );

        $this->get(route('exam-aid', [
            'q' => 'wAvEfOrM',
            'resource_type' => 'video',
        ]))
            ->assertOk()
            ->assertDontSee('Cardiac Resource Bundle')
            ->assertSee('No matching materials found');
    }

    public function test_clearing_search_restores_materials_and_missing_results_are_explained(): void
    {
        $this->createSearchRecords();

        $this->get(route('exam-aid', ['q' => 'not-a-real-material']))
            ->assertOk()
            ->assertSee('No matching materials found')
            ->assertSee('not-a-real-material')
            ->assertDontSee('Cardiac Resource Bundle');

        $this->get(route('exam-aid'))
            ->assertOk()
            ->assertSee('Cardiac Resource Bundle')
            ->assertSee('Respiratory Resource Bundle')
            ->assertDontSee('No matching materials found');
    }

    public function test_exam_resource_cards_keep_view_without_a_download_action_for_any_resource_type(): void
    {
        $records = $this->createSearchRecords();
        $records['aid']->materials()->createMany([
            [
                'title' => 'Document handout',
                'type' => 'document',
                'file_path' => 'exam-aids/document.docx',
            ],
            [
                'title' => 'Reference link',
                'type' => 'link',
                'url' => 'https://example.test/reference',
            ],
        ]);
        $records['aid']->materials()->where('type', 'pdf')->update([
            'file_path' => 'exam-aids/reference.pdf',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('exam-aid'))
            ->assertOk()
            ->assertSee('View')
            ->assertSee('Cardiac Resource Bundle')
            ->assertSee('Respiratory Resource Bundle');

        $this->assertSame(
            2,
            preg_match_all('/<div class="exam-card-actions">(.*?)<\/div>/s', $response->getContent(), $actionGroups)
        );

        foreach ($actionGroups[1] as $actions) {
            $this->assertSame(1, preg_match('/>\s*View\s*<\/button>/', $actions));
            $this->assertStringNotContainsString('Download', $actions);
            $this->assertStringNotContainsString('window.open(', $actions);
        }
    }

    private function createSearchRecords(): array
    {
        $year = AcademicYear::create([
            'name' => 'Year 2',
            'slug' => 'year-2-search',
        ]);
        $subject = Subject::create([
            'name' => 'Clinical Physiology',
            'slug' => 'clinical-physiology-search',
        ]);
        $unit = Unit::create([
            'subject_id' => $subject->id,
            'name' => 'Cardiology',
            'slug' => 'cardiology-search',
        ]);
        $unitTopic = UnitTopic::create([
            'subject_id' => $subject->id,
            'unit_id' => $unit->id,
            'title' => 'Cardiac Action Potential',
            'slug' => 'cardiac-action-potential-search',
        ]);
        $topic = Topic::create([
            'title' => 'Cardiac Electrophysiology',
            'slug' => 'cardiac-electrophysiology-search',
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'unit_topic_id' => $unitTopic->id,
        ]);
        $category = ExamAidCategory::create([
            'name' => 'Cardiac Resource Category',
            'slug' => 'cardiac-resource-category-search',
        ]);
        $aid = ExamAid::create([
            'subject_id' => $subject->id,
            'unit_id' => $unit->id,
            'academic_year_id' => $year->id,
            'title' => 'Cardiac Resource Bundle',
            'description' => 'Reference collection for clinical students.',
        ]);
        ExamAidMaterial::create([
            'exam_aid_id' => $aid->id,
            'exam_aid_category_id' => $category->id,
            'title' => 'ECG Waveform Reference',
            'type' => 'pdf',
            'file_path' => 'exam-aids/waveform.pdf',
            'content' => 'Ventricular tracing manual',
        ]);
        $topic->materials()->create([
            'title' => 'Cardiac Rhythm Notes',
            'type' => 'note',
            'content' => 'Study detail for cardiac action potential',
        ]);

        $otherSubject = Subject::create([
            'name' => 'Respiratory Science',
            'slug' => 'respiratory-science-search',
        ]);
        $otherAid = ExamAid::create([
            'subject_id' => $otherSubject->id,
            'title' => 'Respiratory Resource Bundle',
            'description' => 'A different learning collection.',
        ]);
        ExamAidMaterial::create([
            'exam_aid_id' => $otherAid->id,
            'title' => 'Lung Video',
            'type' => 'video',
            'content' => 'Respiratory tutorial',
        ]);

        $page = Page::create([
            'title' => 'Exam Aid',
            'slug' => 'exam-aid',
            'status' => true,
        ]);
        PageSection::create([
            'page_id' => $page->id,
            'name' => 'Exam Resources',
            'slug' => 'exam-resources',
            'type' => 'exam_resources',
            'content' => [],
            'enabled' => true,
        ]);

        return compact('aid', 'year');
    }
}
