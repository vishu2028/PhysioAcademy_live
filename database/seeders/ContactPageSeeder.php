<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionItem;

class ContactPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::firstOrCreate(['slug' => 'contact-us'], [
            'title' => 'Get in Touch',
            'meta_title' => 'Contact Us - Physio Academy',
            'meta_description' => 'Have questions? We\'re here to help you on your academic journey.',
            'status' => true,
            'is_protected' => false,
        ]);

        // 1. Info Section
        $info = PageSection::updateOrCreate(
            ['page_id' => $page->id, 'slug' => 'contact-info'],
            [
                'name'    => 'Contact Info Details',
                'type'    => 'contact-info',
                'content' => [
                    'email' => 'hello@physiosphere.com',
                    'address' => 'Medical Square, Academic Block, Earth',
                    'phone' => '+1 234 567 890',
                ],
                'order'   => 1,
                'enabled' => true,
            ]
        );

        // 2. Social Links Section
        $socials = PageSection::updateOrCreate(
            ['page_id' => $page->id, 'slug' => 'contact-socials'],
            [
                'name'    => 'Social Links',
                'type'    => 'social-links',
                'content' => [
                    'heading' => 'Follow our journey',
                ],
                'order'   => 2,
                'enabled' => true,
            ]
        );

        PageSectionItem::updateOrCreate(
            ['section_id' => $socials->id, 'title' => 'Twitter'],
            ['body' => 'https://twitter.com', 'meta' => ['icon' => 'bi-twitter-x'], 'enabled' => true]
        );
        PageSectionItem::updateOrCreate(
            ['section_id' => $socials->id, 'title' => 'Instagram'],
            ['body' => 'https://instagram.com', 'meta' => ['icon' => 'bi-instagram'], 'enabled' => true]
        );
        PageSectionItem::updateOrCreate(
            ['section_id' => $socials->id, 'title' => 'LinkedIn'],
            ['body' => 'https://linkedin.com', 'meta' => ['icon' => 'bi-linkedin'], 'enabled' => true]
        );

        $this->command->info('Contact page sections seeded successfully.');
    }
}
