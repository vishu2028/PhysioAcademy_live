<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $page = DB::table('pages')
            ->where('slug', 'exam-aid')
            ->first();

        /*
         * Important:
         * Hum id hardcode nahi kar rahe, kyunki live DB me page id different ho sakti hai.
         * Hum slug = exam-aid se page find kar rahe hain.
         */
        if (!$page) {
            return;
        }

        $sections = [
            [
                'name' => 'Exam Hero',
                'slug' => 'exam-hero',
                'type' => 'exam_hero',
                'order' => 1,
                'enabled' => 1,
                'content' => [
                    'kicker' => 'Exam Aid Studio',
                    'title' => 'Study smarter with exam-ready resources',
                    'description' => 'Find past papers, viva questions, subject notes and exam guides in one place.',
                    'primary_cta_text' => 'Explore Resources',
                    'primary_cta_url' => '#exam-resources',
                    'secondary_cta_text' => 'Search Subjects',
                    'secondary_cta_url' => '#college-selector',
                    'readiness_score' => 92,
                    'quick_links' => [
                        [
                            'icon_num' => '01',
                            'label' => 'Past Papers',
                            'url' => '#exam-resources',
                        ],
                        [
                            'icon_num' => '02',
                            'label' => 'Viva Questions',
                            'url' => '#exam-resources',
                        ],
                        [
                            'icon_num' => '03',
                            'label' => 'Subject Notes',
                            'url' => '#exam-resources',
                        ],
                        [
                            'icon_num' => '04',
                            'label' => 'Exam Guides',
                            'url' => '#exam-resources',
                        ],
                    ],
                    'progress_items' => [
                        [
                            'label' => 'Past Papers',
                            'width' => '85%',
                        ],
                        [
                            'label' => 'Viva Sets',
                            'width' => '70%',
                        ],
                        [
                            'label' => 'Guides',
                            'width' => '92%',
                        ],
                    ],
                    'stats' => [
                        'papers' => 120,
                        'questions' => 850,
                        'guides' => 40,
                    ],
                    'floating_cards' => [
                        'Updated exam resources',
                        'Topper verified notes',
                    ],
                ],
            ],
            [
                'name' => 'Exam Filters',
                'slug' => 'exam-filters',
                'type' => 'exam_filters',
                'order' => 2,
                'enabled' => 1,
                'content' => [
                    'eyebrow' => 'Smart Filters',
                    'title' => 'Find the right study material',
                    'description' => 'Filter resources by university, year and resource type.',
                ],
            ],
            [
                'name' => 'Exam Resources',
                'slug' => 'exam-resources',
                'type' => 'exam_resources',
                'order' => 3,
                'enabled' => 1,
                'content' => [
                    'eyebrow' => 'Resource Library',
                    'title' => 'Exam resources built for students',
                    'description' => 'Explore FAQs, topics, past papers and viva preparation material.',
                ],
            ],
        ];

        foreach ($sections as $section) {
            DB::table('page_sections')->updateOrInsert(
                [
                    'page_id' => $page->id,
                    'slug' => $section['slug'],
                ],
                [
                    'name' => $section['name'],
                    'type' => $section['type'],
                    'content' => json_encode($section['content']),
                    'order' => $section['order'],
                    'enabled' => $section['enabled'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        /*
         * Optional:
         * Agar old wrong sections ko disable karna hai to ye uncomment kar do.
         * Lekin pehle local par test kar lena.
         */
        /*
        DB::table('page_sections')
            ->where('page_id', $page->id)
            ->whereIn('type', [
                'feature-grid',
                'explore-grid',
                'split-section',
                'vision',
                'closing',
            ])
            ->update([
                'enabled' => 0,
                'updated_at' => $now,
            ]);
        */
    }

    public function down(): void
    {
        $page = DB::table('pages')
            ->where('slug', 'exam-aid')
            ->first();

        if (!$page) {
            return;
        }

        DB::table('page_sections')
            ->where('page_id', $page->id)
            ->whereIn('slug', [
                'exam-hero',
                'exam-filters',
                'exam-resources',
            ])
            ->delete();
    }
};
