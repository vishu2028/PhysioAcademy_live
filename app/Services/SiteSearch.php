<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AboutContent;
use App\Models\AboutTimeline;
use App\Models\Announcement;
use App\Models\CommunityActivity;
use App\Models\Banner;
use App\Models\Doubt;
use App\Models\DoubtSessionBooking;
use App\Models\ExamAid;
use App\Models\ExamAidCategory;
use App\Models\ExamPrepCard;
use App\Models\FAQ;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarRequest;
use App\Models\LearningMaterial;
use App\Models\Page;
use App\Models\Subject;
use App\Models\TeamMember;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\UnitTopic;
use App\Models\Webinar;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SiteSearch
{
    public function search(string $search, ?int $perTypeLimit = null): Collection
    {
        $search = $this->normalize($search);
        $terms = $this->terms($search);

        $limit = $perTypeLimit === null
            ? null
            : min(max($perTypeLimit, 1), 100);
        $results = collect()
            ->concat($this->topics($search, $terms, $limit))
            ->concat($this->learningMaterials($search, $terms, $limit))
            ->concat($this->subjects($search, $terms, $limit))
            ->concat($this->academicYears($search, $terms, $limit))
            ->concat($this->units($search, $terms, $limit))
            ->concat($this->unitTopics($search, $terms, $limit))
            ->concat($this->examAidCategories($search, $terms, $limit))
            ->concat($this->examAids($search, $terms, $limit))
            ->concat($this->examPrepCards($search, $terms, $limit))
            ->concat($this->faqs($search, $terms, $limit))
            ->concat($this->pages($search, $terms, $limit))
            ->concat($this->webinars($search, $terms, $limit))
            ->concat($this->announcements($search, $terms, $limit))
            ->concat($this->communityActivities($search, $terms, $limit))
            ->concat($this->aboutTimelines($search, $terms, $limit))
            ->concat($this->aboutContent($search, $terms))
            ->concat($this->teamMembers($search, $terms, $limit))
            ->concat($this->banners($search, $terms, $limit));

        if (auth()->check()) {
            $results = $results
                ->concat($this->ownDoubts($search, $terms, $limit))
                ->concat($this->ownDoubtSessions($search, $terms, $limit))
                ->concat($this->ownGroupWebinars($search, $terms, $limit));
        }

        return $results
            ->sort(function (array $left, array $right): int {
                return [$left['relevance'], $left['title'], $left['id']]
                    <=> [$right['relevance'], $right['title'], $right['id']];
            })
            ->values()
            ->map(function (array $result): array {
                unset($result['relevance']);

                return $result;
            });
    }

    public function trending(int $limit = 8): Collection
    {
        return Topic::trending()
            ->with(['subject:id,name,slug', 'academicYear:id,name,slug'])
            ->orderBy('order')
            ->orderByDesc('updated_at')
            ->limit(min(max($limit, 1), 10))
            ->get()
            ->map(fn (Topic $topic): array => array_replace(
                $this->result(
                    $topic->id,
                    $topic->title,
                    'Topic',
                    $topic->description,
                    route('topics.show', ['slug' => $topic->slug]),
                    $topic->subject,
                    $topic->academicYear,
                    $topic->title,
                    ''
                ),
                [
                    'slug' => $topic->slug,
                    'module_number' => $topic->module_number,
                    'is_protected' => (bool) $topic->is_protected,
                ]
            ));
    }

    private function topics(string $search, array $terms, ?int $limit): Collection
    {
        $query = Topic::query()
            ->active()
            ->whereHas('subject', fn (Builder $subject) => $subject->where('status', true))
            ->with([
                'subject:id,name,slug',
                'academicYear:id,name,slug',
                'unitTopic.unit:id,name,slug',
            ])
            ->when(! auth()->check(), fn (Builder $topics) => $topics->with('materials'));

        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'topics', [
                'title', 'bottom_line', 'module_number', 'slug',
            ], $term);
            if (auth()->check()) {
                $match->orWhereRaw(
                    "LOWER(COALESCE(topics.description, '')) LIKE ?",
                    ['%' . $term . '%']
                );
            } else {
                $match->orWhereRaw(
                    "LOWER(COALESCE(topics.description, '')) LIKE ?",
                    ['%' . $term . '%']
                );
            }
            $match->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                $subject,
                'subjects',
                ['name', 'code', 'description'],
                $term
            ));
            $match->orWhereHas('academicYear', fn (Builder $year) => $this->columns(
                $year,
                'academic_years',
                ['name', 'description'],
                $term
            ));
            $match->orWhereHas('unitTopic', fn (Builder $unitTopic) => $this->columns(
                $unitTopic,
                'unit_topics',
                ['title'],
                $term
            ));
            $match->orWhereHas('unitTopic.unit', fn (Builder $unit) => $this->columns(
                $unit,
                'units',
                ['name', 'description'],
                $term
            ));
            $match->orWhereHas('materials', function (Builder $material) use ($term): void {
                $material->where(function (Builder $materialMatches) use ($term): void {
                    $this->columns($materialMatches, 'learning_materials', ['title', 'type'], $term);
                    if (auth()->check()) {
                        $materialMatches->orWhere(function (Builder $privateText) use ($term): void {
                            $this->columns($privateText, 'learning_materials', ['content', 'url'], $term);
                        });
                        $materialMatches->orWhereHas('category', fn (Builder $category) => $this->columns(
                            $category,
                            'exam_aid_categories',
                            ['name', 'description'],
                            $term
                        ));
                    } else {
                        $materialMatches->orWhere(function (Builder $publicNote) use ($term): void {
                            $publicNote
                                ->where('learning_materials.type', 'note')
                                ->whereRaw(
                                    "LOWER(COALESCE(learning_materials.content, '')) LIKE ?",
                                    ['%' . $term . '%']
                                );
                        });
                    }
                });
            });
        });

        $topics = $this->orderByTitleRelevance($query, 'topics.title', $search)
            ->when($limit !== null && auth()->check(), fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->when(! auth()->check(), fn (Collection $topics) => $topics->filter(
                fn (Topic $topic): bool => $this->topicVisibleMatch($topic, $terms)
            ));

        if ($limit !== null) {
            $topics = $topics->take($limit);
        }

        return $topics->map(fn (Topic $topic): array => array_replace(
                $this->result(
                    $topic->id,
                    $topic->title,
                    'Topic',
                    $this->topicDescription($topic),
                    route('topics.show', ['slug' => $topic->slug]),
                    $topic->subject,
                    $topic->academicYear,
                    $topic->title,
                    $this->topicDescription($topic),
                    $search,
                    $topic->unitTopic?->unit
                ),
                [
                    'slug' => $topic->slug,
                    'module_number' => $topic->module_number,
                    'is_protected' => (bool) $topic->is_protected,
                ]
            ));
    }

    private function learningMaterials(string $search, array $terms, ?int $limit): Collection
    {
        $query = LearningMaterial::query()
            ->whereHas('topic', function (Builder $topic): void {
                $topic->active()
                    ->whereHas('subject', fn (Builder $subject) => $subject->where('status', true));
            })
            ->with([
                'topic.subject:id,name,slug',
                'topic.academicYear:id,name,slug',
                'topic.unitTopic.unit:id,name,slug',
            ])
            ->when(auth()->check(), fn (Builder $materials) => $materials->with('category:id,name,slug'));

        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'learning_materials', ['title', 'type'], $term);
            if (auth()->check()) {
                $match->orWhere(function (Builder $privateText) use ($term): void {
                    $this->columns($privateText, 'learning_materials', ['content', 'url'], $term);
                });
                $match->orWhereHas('category', fn (Builder $category) => $this->columns(
                    $category,
                    'exam_aid_categories',
                    ['name', 'description'],
                    $term
                ));
            } else {
                $match->orWhere(function (Builder $publicNote) use ($term): void {
                    $publicNote
                        ->where('learning_materials.type', 'note')
                        ->whereRaw(
                            "LOWER(COALESCE(learning_materials.content, '')) LIKE ?",
                            ['%' . $term . '%']
                        );
                });
            }
            $match->orWhereHas('topic', function (Builder $topic) use ($term): void {
                $this->columns($topic, 'topics', ['title', 'bottom_line'], $term);
                if (auth()->check()) {
                    $topic->orWhereRaw(
                        "LOWER(COALESCE(topics.description, '')) LIKE ?",
                        ['%' . $term . '%']
                    );
                }
                $topic->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                    $subject,
                    'subjects',
                    ['name', 'code', 'description'],
                    $term
                ));
                $topic->orWhereHas('academicYear', fn (Builder $year) => $this->columns(
                    $year,
                    'academic_years',
                    ['name', 'description'],
                    $term
                ));
                $topic->orWhereHas('unitTopic.unit', fn (Builder $unit) => $this->columns(
                    $unit,
                    'units',
                    ['name', 'description'],
                    $term
                ));
            });
        });

        return $this->orderByTitleRelevance($query, 'learning_materials.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(function (LearningMaterial $material) use ($search): array {
                $topic = $material->topic;
                $description = $material->type === 'note' && $material->content
                    ? $material->content
                    : ($topic?->description ?: $topic?->title);
                if (! auth()->check() && $material->type !== 'note' && $topic?->description) {
                    $description = \App\Support\HtmlPreview::half($topic->description);
                }

                return $this->result(
                    $material->id,
                    $material->title,
                    'Learning Material',
                    $description,
                    route('topics.show', ['slug' => $topic->slug]) . '#learning-material-' . $material->id,
                    $topic?->subject,
                    $topic?->academicYear,
                    $material->title,
                    $description,
                    $search,
                    $topic?->unitTopic?->unit
                );
            });
    }

    private function subjects(string $search, array $terms, ?int $limit): Collection
    {
        $query = Subject::active();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'subjects',
            ['name', 'code', 'description'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'subjects.name', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Subject $subject): array => $this->result(
                $subject->id,
                $subject->name,
                'Subject',
                $subject->description ?: $subject->code,
                route('topics.index') . '#subject-' . $subject->id,
                $subject,
                null,
                $subject->name,
                $subject->description
            ));
    }

    private function academicYears(string $search, array $terms, ?int $limit): Collection
    {
        $query = AcademicYear::active();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'academic_years',
            ['name', 'description'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'academic_years.name', $search)
            ->orderBy('academic_years.order')
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (AcademicYear $year): array => $this->result(
                $year->id,
                $year->name,
                'Academic Year',
                $year->description,
                route('topics.year', ['year' => $year->slug]),
                null,
                $year,
                $year->name,
                $year->description
            ));
    }

    private function units(string $search, array $terms, ?int $limit): Collection
    {
        $query = Unit::query()
            ->where('is_active', true)
            ->whereHas('subject', fn (Builder $subject) => $subject->where('status', true))
            ->with('subject:id,name,slug');

        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'units', ['name', 'description'], $term);
            $match->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                $subject,
                'subjects',
                ['name', 'code', 'description'],
                $term
            ));
        });

        return $this->orderByTitleRelevance($query, 'units.name', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Unit $unit): array => $this->result(
                $unit->id,
                $unit->name,
                'Unit',
                $unit->description ?: $unit->subject?->name,
                route('topics.index') . '#unit-panel-' . $unit->subject_id . '-' . $unit->id,
                $unit->subject,
                null,
                $unit->name,
                $unit->description
            ));
    }

    private function unitTopics(string $search, array $terms, ?int $limit): Collection
    {
        $query = UnitTopic::query()
            ->active()
            ->whereHas('unit', fn (Builder $unit) => $unit->where('is_active', true))
            ->whereHas('subject', fn (Builder $subject) => $subject->where('status', true))
            ->with([
                'subject:id,name,slug',
                'unit:id,name,slug',
                'lmsTopics' => fn ($topics) => $topics->active()->orderBy('order'),
            ]);

        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'unit_topics', ['title', 'slug'], $term);
            $match->orWhereHas('unit', fn (Builder $unit) => $this->columns(
                $unit,
                'units',
                ['name', 'description'],
                $term
            ));
            $match->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                $subject,
                'subjects',
                ['name', 'code', 'description'],
                $term
            ));
        });

        return $this->orderByTitleRelevance($query, 'unit_topics.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(function (UnitTopic $unitTopic) use ($search): array {
                $topic = $unitTopic->lmsTopics->first();
                $url = $topic
                    ? route('topics.show', ['slug' => $topic->slug])
                    : route('topics.index') . '#unit-panel-' . $unitTopic->subject_id . '-' . $unitTopic->unit_id;

                return $this->result(
                    $unitTopic->id,
                    $unitTopic->title,
                    'Unit Topic',
                    $unitTopic->unit?->name,
                    $url,
                    $unitTopic->subject,
                    null,
                    $unitTopic->title,
                    $unitTopic->unit?->name,
                    $search,
                    $unitTopic->unit
                );
            });
    }

    private function examAids(string $search, array $terms, ?int $limit): Collection
    {
        $query = ExamAid::query()
            ->where('status', true)
            ->with([
                'subject:id,name,slug',
                'unit:id,name,slug',
                'academicYear:id,name,slug',
            ])
            ->when(auth()->check(), fn (Builder $aids) => $aids->with('materials.category'));

        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'exam_aids', ['title', 'description'], $term);
            if (auth()->check()) {
                $match->orWhere(function (Builder $privateQuestions) use ($term): void {
                    $this->columns($privateQuestions, 'exam_aids', ['viva_question', 'exam_question'], $term);
                });
            }
            $match->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                $subject,
                'subjects',
                ['name', 'code', 'description'],
                $term
            ));
            $match->orWhereHas('unit', fn (Builder $unit) => $this->columns(
                $unit,
                'units',
                ['name', 'description'],
                $term
            ));
            $match->orWhereHas('academicYear', fn (Builder $year) => $this->columns(
                $year,
                'academic_years',
                ['name', 'description'],
                $term
            ));
            if (auth()->check()) {
                $match->orWhereHas('materials', function (Builder $material) use ($term): void {
                    $this->columns($material, 'exam_aid_materials', [
                        'title', 'content', 'type', 'url',
                    ], $term);
                    $material->orWhereHas('category', fn (Builder $category) => $this->columns(
                        $category,
                        'exam_aid_categories',
                        ['name', 'description'],
                        $term
                    ));
                });
            }
        });

        return $this->orderByTitleRelevance($query, 'exam_aids.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(function (ExamAid $aid) use ($search): array {
                $matchedMaterial = auth()->check()
                    ? $aid->materials->first(function ($material) use ($search): bool {
                        $text = mb_strtolower(strip_tags(
                            $material->title . ' ' . ($material->content ?? '') . ' ' . ($material->category?->name ?? '')
                        ));

                        return collect($this->terms($search))->every(
                            fn (string $term): bool => str_contains($text, $term)
                        );
                    })
                    : null;
                $description = $matchedMaterial
                    ? trim($matchedMaterial->title . ' ' . strip_tags($matchedMaterial->content ?? ''))
                    : ($aid->description ?: $aid->viva_question ?: $aid->exam_question);
                $url = route('exam-aid', [
                    'q' => $aid->title,
                    'exam_aid' => $aid->id,
                ]) . '#exam-aid-card-' . $aid->id;

                return $this->result(
                    $aid->id,
                    $aid->title,
                    'Exam Aid',
                    $description ?: $aid->unit?->name,
                    $url,
                    $aid->subject,
                    $aid->academicYear,
                    $aid->title,
                    $description,
                    $search,
                    $aid->unit
                );
            });
    }

    private function faqs(string $search, array $terms, ?int $limit): Collection
    {
        $query = FAQ::active();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'faqs',
            ['question', 'answer', 'category'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'faqs.question', $search)
            ->orderBy('faqs.order')
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (FAQ $faq): array => $this->result(
                $faq->id,
                $faq->question,
                'FAQ',
                $faq->answer,
                route('faq') . '#faq-' . $faq->id,
                null,
                null,
                $faq->question,
                $faq->answer,
                $search
            ));
    }

    private function examAidCategories(string $search, array $terms, ?int $limit): Collection
    {
        $query = ExamAidCategory::active();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'exam_aid_categories',
            ['name', 'description'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'exam_aid_categories.name', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (ExamAidCategory $category): array => $this->result(
                $category->id,
                $category->name,
                'Exam Aid Category',
                $category->description,
                route('exam-aid.category', ['slug' => $category->slug]),
                null,
                null,
                $category->name,
                $category->description,
                $search
            ));
    }

    private function examPrepCards(string $search, array $terms, ?int $limit): Collection
    {
        if (! \App\Http\Controllers\Admin\ExamPrepController::settings()['exam_prep_enabled']) {
            return collect();
        }

        $query = ExamPrepCard::active()->with('category');
        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'exam_prep_cards', ['title', 'description'], $term);
            $match->orWhereHas('category', fn (Builder $category) => $this->columns(
                $category,
                'exam_aid_categories',
                ['name', 'description'],
                $term
            ));
        });

        return $this->orderByTitleRelevance($query, 'exam_prep_cards.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (ExamPrepCard $card): array => $this->result(
                $card->id,
                $card->title,
                'Exam Prep',
                $card->description ?: $card->category?->name,
                $card->link_url,
                null,
                null,
                $card->title,
                $card->description,
                $search
            ));
    }

    private function pages(string $search, array $terms, ?int $limit): Collection
    {
        $query = Page::active()
            ->when(! auth()->check(), fn (Builder $pages) => $pages->where('is_protected', false));

        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'pages', ['title', 'meta_title', 'meta_description'], $term);
            if (auth()->check()) {
                $match->orWhereRaw(
                    "LOWER(COALESCE(pages.content, '')) LIKE ?",
                    ['%' . $term . '%']
                );
                $match->orWhereHas('sections', function (Builder $section) use ($term): void {
                    $section->where('enabled', true)->where(function (Builder $sectionMatch) use ($term): void {
                        $this->columns($sectionMatch, 'page_sections', ['name', 'slug', 'type'], $term);
                        $sectionMatch->orWhereRaw(
                            'LOWER(CAST(page_sections.content AS CHAR)) LIKE ?',
                            ['%' . $term . '%']
                        );
                        $sectionMatch->orWhereHas('items', function (Builder $item) use ($term): void {
                            $item->where('enabled', true)->where(function (Builder $itemMatch) use ($term): void {
                                $this->columns($itemMatch, 'page_section_items', ['title', 'body'], $term);
                                $itemMatch->orWhereRaw(
                                    'LOWER(CAST(page_section_items.meta AS CHAR)) LIKE ?',
                                    ['%' . $term . '%']
                                );
                            });
                        });
                    });
                });
            }
        });

        return $this->orderByTitleRelevance($query, 'pages.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Page $page): array => $this->result(
                $page->id,
                $page->title,
                'Published Page',
                $page->meta_description ?: (auth()->check() ? $page->content : null),
                $page->url,
                null,
                null,
                $page->title,
                auth()->check() ? ($page->content ?: $page->meta_description) : $page->meta_description,
                $search
            ));
    }

    private function webinars(string $search, array $terms, ?int $limit): Collection
    {
        $query = Webinar::published();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'webinars',
            [
                'title', 'description', 'learning_objectives', 'subject',
                'academic_year', 'semester', 'speaker',
            ],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'webinars.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Webinar $webinar): array => $this->result(
                $webinar->id,
                $webinar->title,
                'Webinar',
                $webinar->description ?: $webinar->learning_objectives,
                route('webinars.show', ['webinar' => $webinar->slug]),
                $webinar->subject ? ['name' => $webinar->subject] : null,
                $webinar->academic_year ? ['name' => $webinar->academic_year] : null,
                $webinar->title,
                $webinar->description ?: $webinar->learning_objectives,
                $search
            ));
    }

    private function announcements(string $search, array $terms, ?int $limit): Collection
    {
        $query = Announcement::query();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'announcements',
            ['title', 'date'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'announcements.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Announcement $announcement): array => $this->result(
                $announcement->id,
                $announcement->title,
                'Announcement',
                $announcement->date,
                route('home') . '#announcement-' . $announcement->id,
                null,
                null,
                $announcement->title,
                $announcement->date,
                $search
            ));
    }

    private function communityActivities(string $search, array $terms, ?int $limit): Collection
    {
        $query = CommunityActivity::query();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'community_activities',
            ['title', 'subject', 'time'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'community_activities.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (CommunityActivity $activity): array => $this->result(
                $activity->id,
                $activity->title,
                'Community Activity',
                trim($activity->time . ' · ' . $activity->subject, ' ·'),
                route('home') . '#community-activity-' . $activity->id,
                null,
                null,
                $activity->title,
                $activity->subject . ' ' . $activity->time,
                $search
            ));
    }

    private function aboutTimelines(string $search, array $terms, ?int $limit): Collection
    {
        $query = AboutTimeline::query();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'about_timelines',
            ['year', 'title', 'description'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'about_timelines.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (AboutTimeline $timeline): array => $this->result(
                $timeline->id,
                $timeline->title,
                'About PhysioSource',
                $timeline->description,
                route('home') . '#timeline-entry-' . $timeline->id,
                null,
                null,
                $timeline->title,
                $timeline->description . ' ' . $timeline->year,
                $search
            ));
    }

    private function aboutContent(string $search, array $terms): Collection
    {
        $query = AboutContent::query()->orderBy('id');
        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'about_contents', ['main_title'], $term);
            if (auth()->check()) {
                $match->orWhereRaw(
                    "LOWER(COALESCE(about_contents.main_description, '')) LIKE ?",
                    ['%' . $term . '%']
                );
            } else {
                $match->orWhereRaw(
                    "LOWER(SUBSTR(COALESCE(about_contents.main_description, ''), 1, 80)) LIKE ?",
                    ['%' . $term . '%']
                );
            }
        });

        $about = $query->first();

        return $about ? collect([
            $this->result(
                $about->id,
                strip_tags($about->main_title),
                'About PhysioSource',
                auth()->check()
                    ? $about->main_description
                    : Str::limit(strip_tags($about->main_description), 80),
                route('home') . '#about',
                null,
                null,
                strip_tags($about->main_title),
                $about->main_description,
                $search
            ),
        ]) : collect();
    }

    private function teamMembers(string $search, array $terms, ?int $limit): Collection
    {
        $query = TeamMember::active();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'team_members',
            ['name', 'designation', 'bio'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'team_members.name', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (TeamMember $member): array => $this->result(
                $member->id,
                $member->name,
                'Team Member',
                $member->designation . ($member->bio ? ' · ' . $member->bio : ''),
                route('about') . '#team-member-' . $member->id,
                null,
                null,
                $member->name,
                $member->designation . ' ' . $member->bio,
                $search
            ));
    }

    private function banners(string $search, array $terms, ?int $limit): Collection
    {
        $query = Banner::active();
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'banners',
            ['title', 'description'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'banners.title', $search)
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Banner $banner): array => $this->result(
                $banner->id,
                $banner->title,
                'Site Information',
                $banner->description,
                $banner->link ?: route('home'),
                null,
                null,
                $banner->title,
                $banner->description,
                $search
            ));
    }

    private function ownDoubts(string $search, array $terms, ?int $limit): Collection
    {
        $query = Doubt::query()
            ->where('user_id', auth()->id())
            ->with(['subject:id,name,slug', 'academicYear:id,name,slug']);
        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'doubts', ['topic', 'message', 'answer'], $term);
            $match->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                $subject,
                'subjects',
                ['name'],
                $term
            ));
            $match->orWhereHas('messages', fn (Builder $message) => $this->columns(
                $message,
                'doubt_messages',
                ['message'],
                $term
            ));
        });

        return $this->orderByTitleRelevance($query, 'doubts.topic', $search)
            ->orderByDesc('doubts.updated_at')
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (Doubt $doubt): array => $this->result(
                $doubt->id,
                $doubt->topic ?: 'Student doubt',
                'Student Doubt',
                $doubt->message ?: $doubt->answer,
                route('doubts.show', ['doubt' => $doubt->id]),
                $doubt->subject,
                $doubt->academicYear,
                $doubt->topic ?: 'Student doubt',
                $doubt->message ?: $doubt->answer,
                $search
            ));
    }

    private function ownDoubtSessions(string $search, array $terms, ?int $limit): Collection
    {
        $query = DoubtSessionBooking::query()
            ->where('user_id', auth()->id())
            ->with(['subject:id,name,slug', 'academicYear:id,name,slug']);
        $this->whereTerms($query, $terms, function (Builder $match, string $term): void {
            $this->columns($match, 'doubt_session_bookings', [
                'booking_reference', 'topic', 'doubt',
            ], $term);
            $match->orWhereHas('subject', fn (Builder $subject) => $this->columns(
                $subject,
                'subjects',
                ['name'],
                $term
            ));
            $match->orWhereHas('academicYear', fn (Builder $year) => $this->columns(
                $year,
                'academic_years',
                ['name'],
                $term
            ));
        });

        return $this->orderByTitleRelevance($query, 'doubt_session_bookings.topic', $search)
            ->orderByDesc('doubt_session_bookings.updated_at')
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (DoubtSessionBooking $booking): array => $this->result(
                $booking->id,
                $booking->topic ?: 'Doubt session ' . $booking->booking_reference,
                'Doubt Session',
                $booking->doubt,
                route('doubt-sessions.history'),
                $booking->subject,
                $booking->academicYear,
                $booking->topic ?: $booking->booking_reference,
                $booking->doubt,
                $search
            ));
    }

    private function ownGroupWebinars(string $search, array $terms, ?int $limit): Collection
    {
        $query = GroupWebinarRequest::query()
            ->where('status', GroupWebinarRequest::STATUS_CONFIRMED)
            ->whereNotNull('meeting_link')
            ->whereHas('participants', function (Builder $participants): void {
                $participants
                    ->where('user_id', auth()->id())
                    ->whereIn('payment_status', [
                        GroupWebinarParticipant::PAYMENT_PAID,
                        GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
                    ]);
            });
        $this->whereTerms($query, $terms, fn (Builder $match, string $term) => $this->columns(
            $match,
            'group_webinar_requests',
            ['topic', 'description'],
            $term
        ));

        return $this->orderByTitleRelevance($query, 'group_webinar_requests.topic', $search)
            ->orderByDesc('group_webinar_requests.scheduled_at')
            ->when($limit !== null, fn (Builder $builder) => $builder->limit($limit))
            ->get()
            ->map(fn (GroupWebinarRequest $webinar): array => $this->result(
                $webinar->id,
                $webinar->topic,
                'Group Webinar',
                $webinar->description,
                route('group-webinars.join', ['groupWebinar' => $webinar->id]),
                null,
                null,
                $webinar->topic,
                $webinar->description,
                $search
            ));
    }

    private function whereTerms(Builder $query, array $terms, Closure $addAlternatives): void
    {
        if ($terms === []) {
            return;
        }

        $query->where(function (Builder $allTerms) use ($terms, $addAlternatives): void {
            foreach ($terms as $term) {
                $allTerms->where(function (Builder $alternatives) use ($term, $addAlternatives): void {
                    $addAlternatives($alternatives, $term);
                });
            }
        });
    }

    private function columns(Builder $query, string $table, array $columns, string $term): void
    {
        foreach ($columns as $index => $column) {
            $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
            $query->{$method}(
                "LOWER(COALESCE({$table}.{$column}, '')) LIKE ?",
                ['%' . $term . '%']
            );
        }
    }

    private function orderByTitleRelevance(Builder $query, string $column, string $search): Builder
    {
        $search = mb_strtolower($search);

        return $query->orderByRaw(
            "CASE
                WHEN LOWER(COALESCE({$column}, '')) = ? THEN 0
                WHEN LOWER(COALESCE({$column}, '')) LIKE ? THEN 1
                WHEN LOWER(COALESCE({$column}, '')) LIKE ? THEN 2
                ELSE 3
            END",
            [$search, $search . '%', '%' . $search . '%']
        )->orderBy($column);
    }

    private function result(
        int $id,
        string $title,
        string $category,
        ?string $description,
        string $url,
        mixed $subject,
        mixed $academicYear,
        string $titleForRank,
        ?string $contentForRank,
        string $search = '',
        mixed $unit = null
    ): array {
        $subject = $this->relation($subject);
        $academicYear = $this->relation($academicYear);
        $unit = $this->relation($unit);

        return [
            'id' => $id,
            'title' => $title,
            'category' => $category,
            'description' => $this->excerpt($description),
            'url' => $url,
            'slug' => null,
            'subject' => $subject,
            'unit' => $unit,
            'academic_year' => $academicYear,
            'year' => $academicYear['name'] ?? null,
            'status' => 'available',
            'relevance' => $this->relevance($titleForRank, $contentForRank, $search),
        ];
    }

    private function relation(mixed $value): ?array
    {
        if (is_string($value) && $value !== '') {
            return ['name' => $value];
        }

        if (! is_object($value)) {
            return null;
        }

        return array_filter([
            'id' => $value->id ?? null,
            'name' => $value->name ?? null,
            'slug' => $value->slug ?? null,
        ], fn ($item) => $item !== null);
    }

    private function topicVisibleMatch(Topic $topic, array $terms): bool
    {
        $fields = [
            $topic->title,
            $topic->bottom_line,
            $topic->module_number,
            $topic->subject?->name,
            $topic->subject?->code,
            $topic->subject?->description,
            $topic->academicYear?->name,
            $topic->academicYear?->description,
            $topic->unitTopic?->title,
            $topic->unitTopic?->unit?->name,
            $topic->unitTopic?->unit?->description,
            $topic->description
                ? \App\Support\HtmlPreview::half($topic->description)
                : null,
        ];

        foreach ($topic->materials as $material) {
            $fields[] = $material->title;
            $fields[] = $material->type;

            if ($material->type === 'note') {
                $fields[] = $material->content;
            }
        }

        $visibleText = mb_strtolower(strip_tags(implode(' ', array_filter($fields))));

        return collect($terms)->every(
            fn (string $term): bool => str_contains($visibleText, $term)
        );
    }

    private function topicDescription(Topic $topic): ?string
    {
        $description = $topic->description ?: $topic->bottom_line;

        if ($topic->description && ! auth()->check()) {
            return \App\Support\HtmlPreview::half($topic->description);
        }

        return $description;
    }

    private function relevance(string $title, ?string $content, string $search): int
    {
        if ($search === '') {
            return 10;
        }

        $title = mb_strtolower(strip_tags($title));
        $search = mb_strtolower($search);

        if ($title === $search) {
            return 0;
        }

        if (str_starts_with($title, $search)) {
            return 1;
        }

        if (str_contains($title, $search)) {
            return 2;
        }

        $terms = $this->terms($search);
        if ($terms !== [] && collect($terms)->every(fn (string $term): bool => str_contains($title, $term))) {
            return 3;
        }

        $content = mb_strtolower(strip_tags($content ?? ''));

        return $terms !== [] && collect($terms)->every(
            fn (string $term): bool => str_contains($content, $term)
        ) ? 4 : 5;
    }

    private function excerpt(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? ''), 180);
    }

    private function normalize(string $search): string
    {
        $search = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $search) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $search) ?? '');
    }

    private function terms(string $search): array
    {
        if ($search === '') {
            return [];
        }

        $terms = array_values(array_unique(array_filter(
            preg_split('/\s+/u', mb_strtolower($search)) ?: [],
            fn (string $term): bool => $term !== ''
        )));

        if (count($terms) > 10) {
            throw ValidationException::withMessages([
                'query' => 'Search accepts up to 10 words at a time.',
            ]);
        }

        return $terms;
    }
}
