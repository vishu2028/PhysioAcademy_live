<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionItem;

class BookmarkPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::firstOrCreate(['slug' => 'bookmarks'], [
            'title' => 'My Saved Items',
            'meta_title' => 'My Bookmarks - Physio Academy',
            'meta_description' => 'Access your saved topics and learning materials.',
            'status' => true,
            'is_protected' => true,
        ]);

        // 1. Hero Section
        $hero = PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'bookmark-hero'],
            [
                'name'    => 'Bookmark Hero',
                'type'    => 'hero',
                'content' => [
                    'title' => 'Saved Content Dashboard',
                    'highlight' => 'Dashboard',
                    'subtitle' => 'Search, filter, sort, open, and remove saved academic content from a single focused workspace.',
                ],
                'order'   => 1,
                'enabled' => true,
            ]
        );

        // 2. Empty State Section
        $emptyState = PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'bookmark-empty-state'],
            [
                'name'    => 'Empty State',
                'type'    => 'empty-state',
                'content' => [
                    'heading' => 'Your space is quiet...',
                    'subtext' => 'Start browsing topics and click the star icon to save resources for later.',
                    'button_text' => 'Explore Materials',
                    'button_url' => '/topics',
                ],
                'order'   => 2,
                'enabled' => true,
            ]
        );
        
         // 3. Guest State Section
        $guestState = PageSection::firstOrCreate(
            ['page_id' => $page->id, 'slug' => 'bookmark-guest-state'],
            [
                'name'    => 'Guest Access',
                'type'    => 'guest-state',
                'content' => [
                    'heading' => 'Sign in to sync your space',
                    'subtext' => 'Join thousands of students who save their study progress, clinical notes, and exam prep resources across devices.',
                ],
                'order'   => 3,
                'enabled' => true,
            ]
        );

        $this->command->info('Bookmark page and sections seeded successfully.');
    }
}
