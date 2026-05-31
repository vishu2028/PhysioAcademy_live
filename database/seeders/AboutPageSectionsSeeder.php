<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionItem;

class AboutPageSectionsSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('slug', 'about-us')->first();

        if (!$page) {
            $this->command->warn('About page (slug: about-us) not found. Run CMSDataSeeder first.');
            return;
        }

        // ------------------------------------------------------------------
        // 1. WHY WE BUILT THIS
        // ------------------------------------------------------------------
        $why = PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'why-we-built-this'],
            [
                'name'    => 'Why We Built This',
                'type'    => 'feature-grid',
                'content' => [
                    'subtext' => 'A modern learning platform should make the syllabus feel navigable, reduce exam anxiety, and present content with the same clarity expected from a polished startup product.',
                ],
                'order'   => 1,
                'enabled' => true,
            ]
        );

        $whyItems = [
            ['title' => 'Simplify the syllabus',         'body' => 'Bring structure to dense academic content with a cleaner, more deliberate learning flow.',                    'meta' => ['icon' => 'bi-check-lg'], 'order' => 1],
            ['title' => 'Support the new curriculum',    'body' => 'Align the platform around the latest needs of physiotherapy students and evolving subjects.',                  'meta' => ['icon' => 'bi-check-lg'], 'order' => 2],
            ['title' => 'Improve answer writing',        'body' => 'Make exam preparation more actionable with focused support for structured responses.',                         'meta' => ['icon' => 'bi-check-lg'], 'order' => 3],
            ['title' => 'Provide structured learning',   'body' => 'Organize knowledge into approachable, modern pathways instead of scattered notes.',                           'meta' => ['icon' => 'bi-check-lg'], 'order' => 4],
            ['title' => 'Reduce confusion during exams', 'body' => 'Offer a calmer, more navigable experience for revision, recall, and topic discovery.',                        'meta' => ['icon' => 'bi-check-lg'], 'order' => 5],
            ['title' => 'Create a student-driven platform', 'body' => "Let student demand shape the roadmap so the product stays relevant to real learning needs.",               'meta' => ['icon' => 'bi-check-lg'], 'order' => 6],
        ];

        foreach ($whyItems as $item) {
            PageSectionItem::firstOrCreate(
                ['section_id' => $why->id, 'title' => $item['title']],
                array_merge($item, ['enabled' => true])
            );
        }

        // ------------------------------------------------------------------
        // 2. WHAT YOU CAN EXPLORE
        // ------------------------------------------------------------------
        $explore = PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'what-you-can-explore'],
            [
                'name'    => 'What You Can Explore',
                'type'    => 'explore-grid',
                'content' => [
                    'subtext' => 'Navigate by syllabus, search topics quickly, practice from exam resources, and move beyond theory with clear clinical relevance.',
                ],
                'order'   => 2,
                'enabled' => true,
            ]
        );

        $exploreItems = [
            ['title' => 'Navigate by Syllabus',      'body' => 'Follow a structured path tailored to your university curriculum.',                             'meta' => ['icon' => 'bi-folder2-open'],  'order' => 1],
            ['title' => 'Search Topics Easily',      'body' => 'Find specific physiotherapy concepts in seconds with a focused search experience.',            'meta' => ['icon' => 'bi-search'],        'order' => 2],
            ['title' => 'Access Question Bank',      'body' => 'Practice from a comprehensive repository of previous exam questions.',                         'meta' => ['icon' => 'bi-book'],          'order' => 3],
            ['title' => 'Learn Answer Writing',      'body' => 'Master the art of presenting clinical answers to score higher in exams.',                      'meta' => ['icon' => 'bi-pencil-square'], 'order' => 4],
            ['title' => 'Ask Academic Doubts',       'body' => 'Get help from the community and simplify tricky concepts faster.',                             'meta' => ['icon' => 'bi-chat-dots'],     'order' => 5],
            ['title' => 'Request Important Topics',  'body' => "Tell us what's difficult, and we'll prioritize the content you need most.",                    'meta' => ['icon' => 'bi-megaphone'],     'order' => 6],
        ];

        foreach ($exploreItems as $item) {
            PageSectionItem::firstOrCreate(
                ['section_id' => $explore->id, 'title' => $item['title']],
                array_merge($item, ['enabled' => true])
            );
        }

        // ------------------------------------------------------------------
        // 3. BUILT AROUND STUDENT NEEDS
        // ------------------------------------------------------------------
        $student = PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'built-around-student-needs'],
            [
                'name'    => 'Built Around Student Needs',
                'type'    => 'split-section',
                'content' => [
                    'heading' => 'Built Around Student Needs',
                    'body'    => "We believe the best learning platform is one built by the people using it. Our roadmap isn't static; topics are prioritized based on real-time student requests.",
                ],
                'order'   => 3,
                'enabled' => true,
            ]
        );

        $studentItems = [
            ['title' => 'Brachial Plexus',    'body' => 'High student demand', 'meta' => ['count' => '42'], 'order' => 1],
            ['title' => 'Gait Cycle',          'body' => 'Revision priority',  'meta' => ['count' => '36'], 'order' => 2],
            ['title' => 'UMN vs LMN Lesions', 'body' => 'Exam focus topic',   'meta' => ['count' => '31'], 'order' => 3],
            ["title" => "Erb's Palsy",         'body' => 'Clinical relevance', 'meta' => ['count' => '27'], 'order' => 4],
        ];

        foreach ($studentItems as $item) {
            PageSectionItem::firstOrCreate(
                ['section_id' => $student->id, 'title' => $item['title']],
                array_merge($item, ['enabled' => true])
            );
        }

        // ------------------------------------------------------------------
        // 4. OUR VISION
        // ------------------------------------------------------------------
        PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'our-vision'],
            [
                'name'    => 'Our Vision',
                'type'    => 'vision-banner',
                'content' => [
                    'heading' => 'Our Vision',
                    'body'    => 'To create a modern academic ecosystem for physiotherapy students that seamlessly combines structured learning, expert academic guidance, and competency-focused education in one accessible place.',
                ],
                'order'   => 4,
                'enabled' => true,
            ]
        );

        // ------------------------------------------------------------------
        // 5. LEARN SMARTER. STUDY WITH CLARITY.
        // ------------------------------------------------------------------
        PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'learn-smarter-study-with-clarity'],
            [
                'name'    => 'Learn Smarter. Study with Clarity.',
                'type'    => 'closing-banner',
                'content' => [
                    'kicker'  => 'Closing Banner',
                    'heading' => 'Learn Smarter. Study with Clarity.',
                ],
                'order'   => 5,
                'enabled' => true,
            ]
        );

        $this->command->info('About page sections seeded successfully.');
    }
}
