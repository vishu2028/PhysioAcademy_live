<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HeroSection;
use App\Models\Feature;
use App\Models\FAQ;
use App\Models\Setting;
use App\Models\Banner;
use App\Models\Testimonial;
use App\Models\Page;
use App\Models\Message;
use App\Models\Slider;

class CMSDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedHeroSections();
        $this->seedFeatures();
        $this->seedFAQs();
        $this->seedBanners();
        $this->seedTestimonials();
        $this->seedPages();
        $this->seedMessages();
        $this->seedSliders();
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Physio Academy', 'label' => 'Site Name', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Your Academic Guide for Physiotherapy', 'label' => 'Site Description', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'contact@physioacademy.com', 'label' => 'Site Email', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '+91 98765 43210', 'label' => 'Site Phone', 'group' => 'general'],
            ['key' => 'primary_color', 'value' => '#2563eb', 'label' => 'Primary Color', 'group' => 'appearance'],
            ['key' => 'secondary_color', 'value' => '#38bdf8', 'label' => 'Secondary Color', 'group' => 'appearance'],
            ['key' => 'hero_badge', 'value' => 'New Curriculum 2024 — Fully Updated', 'label' => 'Hero Badge', 'group' => 'hero'],
            ['key' => 'hero_title', 'value' => 'Your Academic Guide for Physiotherapy', 'label' => 'Hero Title', 'group' => 'hero'],
            ['key' => 'hero_subtitle', 'value' => 'Navigate your syllabus, understand important topics, improve answer writing, and get academic support — all in one place.', 'label' => 'Hero Subtitle', 'group' => 'hero'],
            ['key' => 'footer_text', 'value' => 'Empowering future physiotherapists with academic excellence and clinical insights.', 'label' => 'Footer Text', 'group' => 'general'],
            ['key' => 'facebook_url', 'value' => '#', 'label' => 'Facebook URL', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => '#', 'label' => 'Instagram URL', 'group' => 'social'],
            ['key' => 'linkedin_url', 'value' => '#', 'label' => 'LinkedIn URL', 'group' => 'social'],
            ['key' => 'youtube_url', 'value' => '#', 'label' => 'YouTube URL', 'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function seedHeroSections(): void
    {
        HeroSection::firstOrCreate(['title' => 'Your Academic Guide for Physiotherapy'], [
            'subtitle' => 'Navigate your syllabus, understand important topics, improve answer writing, and get academic support — all in one place.',
            'button_text' => 'Explore Topics',
            'button_url' => '#topics',
            'status' => true,
        ]);
    }


    private function seedFeatures(): void
    {
        $features = [
            ['title' => 'Secure Authentication', 'description' => 'Role-based access with encrypted credentials and session management.', 'icon' => 'bi bi-shield-lock'],
            ['title' => 'Smart Dashboard', 'description' => 'Real-time analytics and personalized learning progress tracking.', 'icon' => 'bi bi-speedometer2'],
            ['title' => 'Dynamic Content', 'description' => 'Manage pages, heroes, and services from a powerful admin panel.', 'icon' => 'bi bi-gear-wide-connected'],
            ['title' => 'Mobile Responsive', 'description' => 'Optimized experience across all devices — desktop, tablet, and mobile.', 'icon' => 'bi bi-phone'],
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate(['title' => $feature['title']], $feature);
        }
    }

    private function seedFAQs(): void
    {
        $faqs = [
            ['question' => 'What is Physio Academy?', 'answer' => 'Physio Academy is a comprehensive academic platform designed specifically for physiotherapy students. It provides syllabus navigation, exam preparation tools, answer writing guides, and clinical notes.', 'order' => 1],
            ['question' => 'Is this platform free to use?', 'answer' => 'Yes, the basic features are completely free. We offer premium plans for advanced features like video lectures, personalized mentoring, and downloadable study materials.', 'order' => 2],
            ['question' => 'How do I change the site theme?', 'answer' => 'Administrators can change primary colors, logos, and other visual settings from the Admin Dashboard under Site Settings.', 'order' => 3],
            ['question' => 'Can I download study materials?', 'answer' => 'Premium members can download PDFs, exam prep sheets, and clinical notes. Free members can access all online content.', 'order' => 4],
            ['question' => 'How can I contact support?', 'answer' => 'You can reach us via the Contact form on our website, or email us at contact@physioacademy.com. We typically respond within 24 hours.', 'order' => 5],
        ];

        foreach ($faqs as $faq) {
            FAQ::firstOrCreate(['question' => $faq['question']], $faq);
        }
    }

    private function seedBanners(): void
    {
        Banner::firstOrCreate(['title' => 'Limited Offer: Get 50% off on all Premium Plans!'], [
            'image_path' => 'banners/sale.jpg',
            'link' => '/register',
            'status' => true,
        ]);

        Banner::firstOrCreate(['title' => 'New: Complete Anatomy Study Pack Available'], [
            'image_path' => 'banners/anatomy.jpg',
            'link' => '#',
            'status' => true,
        ]);
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'client_name' => 'Dr. Ananya Verma',
                'client_designation' => 'BPT Graduate, AIIMS Delhi',
                'content' => 'Physio Academy was my go-to resource during university exams. The structured answer guides helped me score consistently well.',
                'rating' => 5,
            ],
            [
                'client_name' => 'Rahul Mehta',
                'client_designation' => 'Final Year BPT Student',
                'content' => 'The question bank is incredibly well-organized. It saved me hours of preparation time and I cleared my finals with flying colors.',
                'rating' => 5,
            ],
            [
                'client_name' => 'Dr. Sneha Patil',
                'client_designation' => 'MPT Orthopaedics',
                'content' => 'As a postgrad student, I still refer to Physio Academy for clinical notes. The content quality is outstanding and regularly updated.',
                'rating' => 4,
            ],
            [
                'client_name' => 'Karthik Reddy',
                'client_designation' => 'BPT, 3rd Year Student',
                'content' => 'The topic navigation feature is brilliant. I can easily find what I need to study for any subject or exam.',
                'rating' => 5,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::firstOrCreate(['client_name' => $testimonial['client_name']], $testimonial);
        }
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h2>About Physio Academy</h2><p>Physio Academy is a premier online platform dedicated to helping physiotherapy students excel in their academics. Founded by a team of experienced physiotherapy educators, our mission is to make quality study resources accessible to every BPT and MPT student.</p><p>We believe in structured learning, comprehensive coverage, and exam-focused preparation that builds both knowledge and confidence.</p>',
                'meta_title' => 'About Physio Academy - Your Academic Guide',
                'meta_description' => 'Learn about Physio Academy, the premier online platform for physiotherapy students.',
                'status' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h2>Get In Touch</h2><p>Have questions or feedback? We would love to hear from you! Reach out to us and our team will get back to you within 24 hours.</p><p>Email: contact@physioacademy.com</p><p>Phone: +91 98765 43210</p>',
                'meta_title' => 'Contact Physio Academy',
                'meta_description' => 'Contact the Physio Academy team for questions, support, or feedback.',
                'status' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2><p>Your privacy is important to us. This policy outlines how we collect, use, and protect your personal information when using Physio Academy.</p><p>We collect only the information necessary to provide our services and never share your data with third parties without your consent.</p>',
                'meta_title' => 'Privacy Policy - Physio Academy',
                'meta_description' => 'Read the Physio Academy privacy policy.',
                'status' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h2>Terms of Service</h2><p>By using Physio Academy, you agree to these terms. Please read them carefully before using our platform.</p><p>All content on this platform is for educational purposes only and should not be used as a substitute for professional medical advice.</p>',
                'meta_title' => 'Terms of Service - Physio Academy',
                'meta_description' => 'Read the terms and conditions for using Physio Academy.',
                'status' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function seedMessages(): void
    {
        $messages = [
            [
                'name' => 'Neha Gupta',
                'email' => 'neha@example.com',
                'subject' => 'Question about Anatomy notes',
                'message' => 'Hi, I was looking for detailed notes on upper limb anatomy. Are they available on the platform? I am preparing for my 1st year exams.',
            ],
            [
                'name' => 'Amit Singh',
                'email' => 'amit@example.com',
                'subject' => 'Premium Plan Inquiry',
                'message' => 'I am interested in upgrading to the premium plan. Could you tell me what additional benefits I would get compared to the free tier?',
            ],
            [
                'name' => 'Fatima Sheikh',
                'email' => 'fatima@example.com',
                'subject' => 'Bug report on mobile',
                'message' => 'I noticed that on my Android phone the dashboard charts are not loading properly. Could you please look into this? Otherwise the platform is fantastic!',
            ],
            [
                'name' => 'Rohan Joshi',
                'email' => 'rohan@example.com',
                'subject' => 'Suggestion for new feature',
                'message' => 'It would be great if you could add a flashcard feature for quick revision. Many students in my class would find it very helpful.',
            ],
            [
                'name' => 'Aditi Nair',
                'email' => 'aditi@example.com',
                'subject' => 'Thank you!',
                'message' => 'I just wanted to say thank you for creating this platform. The answer writing guides helped me tremendously in my exams. Keep up the great work!',
            ],
        ];

        foreach ($messages as $msg) {
            Message::firstOrCreate(
                ['email' => $msg['email'], 'subject' => $msg['subject']],
                $msg
            );
        }
    }

    private function seedSliders(): void
    {
        $sliders = [
            [
                'title' => 'Master Physiotherapy Today',
                'subtitle' => 'Comprehensive study materials from 1st to final year',
                'image_path' => 'sliders/hero-1.jpg',
                'button_text' => 'Start Learning',
                'button_url' => '/register',
                'status' => true,
            ],
            [
                'title' => 'Exam Ready in 30 Days',
                'subtitle' => 'Structured study plans designed by topper physiotherapists',
                'image_path' => 'sliders/hero-2.jpg',
                'button_text' => 'View Plans',
                'button_url' => '#',
                'status' => true,
            ],
        ];

        foreach ($sliders as $slider) {
            Slider::firstOrCreate(['title' => $slider['title']], $slider);
        }
    }
}
