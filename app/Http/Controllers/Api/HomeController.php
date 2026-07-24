<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicYearResource;
use App\Http\Resources\SearchTopicResource;
use App\Models\AcademicYear;
use App\Models\Banner;
use App\Models\ExamAid;
use App\Models\FAQ;
use App\Models\Feature;
use App\Models\HeroSection;
use App\Models\Message;
use App\Models\Slider;
use App\Models\Subject;
use App\Models\Testimonial;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /*
         * Hero section
         */
        $hero = HeroSection::query()
            ->active()
            ->latest('id')
            ->first();

        /*
         * Feature section
         */
        $featureSectionEnabled = Feature::query()
            ->value('section_enabled');

        $featureSectionEnabled = is_null($featureSectionEnabled)
            ? true
            : (bool) $featureSectionEnabled;

        $features = $featureSectionEnabled
            ? Feature::query()
                ->active()
                ->ordered()
                ->get()
            : collect();

        /*
         * Academic years
         */
        $academicYears = AcademicYear::query()
            ->active()
            ->withCurriculumCounts()
            ->orderBy('order')
            ->get();

        /*
         * Trending topics
         */
        $trendingTopics = Topic::query()
            ->active()
            ->whereNull('parent_id')
            ->whereHas('subject', function ($query) {
                $query->where('status', true);
            })
            ->whereHas('academicYear', function ($query) {
                $query->where('status', true);
            })
            ->with([
                'subject:id,name,slug',
                'academicYear:id,name,slug',
            ])
            ->orderBy('order')
            ->limit(4)
            ->get();

        /*
         * Testimonials section
         */
        $testimonialSectionEnabled = Testimonial::query()
            ->value('section_enabled');

        $testimonialSectionEnabled = is_null(
            $testimonialSectionEnabled
        )
            ? true
            : (bool) $testimonialSectionEnabled;

        $testimonials = $testimonialSectionEnabled
            ? Testimonial::query()
                ->active()
                ->ordered()
                ->get()
            : collect();

        /*
         * FAQs, banners and sliders
         */
        $faqs = FAQ::query()
            ->active()
            ->ordered()
            ->limit(6)
            ->get();

        $banners = Banner::query()
            ->active()
            ->ordered()
            ->get();

        $sliders = Slider::query()
            ->active()
            ->ordered()
            ->get();

        /*
         * Most requested subject/topic from contact messages
         */
        $mostRequestedTopic = Message::query()
            ->select('subject')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->groupBy('subject')
            ->orderByDesc('total')
            ->first();

        /*
         * Home statistics
         */
        $vivaQuestionsCount = ExamAid::query()
            ->where('status', true)
            ->whereRaw(
                "TRIM(COALESCE(viva_question, '')) <> ''"
            )
            ->count();

        $examQuestionsCount = ExamAid::query()
            ->where('status', true)
            ->whereRaw(
                "TRIM(COALESCE(exam_question, '')) <> ''"
            )
            ->count();

        return response()->json([
            'data' => [
                'hero' => $hero
                    ? [
                        'id' => $hero->id,
                        'title' => $hero->title,
                        'subtitle' => $hero->subtitle,
                        'badge' => $hero->badge,
                        'button_text' => $hero->button_text,
                        'button_url' => $hero->button_url,
                        'image_url' => $this->fileUrl(
                            $hero->image_path
                        ),
                    ]
                    : null,

                'sections' => [
                    'features_enabled' => $featureSectionEnabled,
                    'testimonials_enabled' => $testimonialSectionEnabled,
                ],

                'features' => $features
                    ->map(function ($feature) {
                        return [
                            'id' => $feature->id,
                            'title' => $feature->title,
                            'description' => $feature->description,
                            'icon' => $feature->icon,
                            'order' => $feature->order,
                        ];
                    })
                    ->values(),

                'academic_years' => AcademicYearResource::collection(
                    $academicYears
                )->resolve($request),

                'trending_topics' => SearchTopicResource::collection(
                    $trendingTopics
                )->resolve($request),

                'testimonials' => $testimonials
                    ->map(function ($testimonial) {
                        return [
                            'id' => $testimonial->id,
                            'client_name' => $testimonial->client_name,
                            'client_designation' => $testimonial
                                ->client_designation,
                            'content' => $testimonial->content,
                            'rating' => $testimonial->rating,
                            'client_image_url' => $this->fileUrl(
                                $testimonial->client_image
                            ),
                            'order' => $testimonial->order,
                        ];
                    })
                    ->values(),

                'faqs' => $faqs
                    ->map(function ($faq) {
                        return [
                            'id' => $faq->id,
                            'question' => $faq->question,
                            'answer' => $faq->answer,
                            'category' => $faq->category,
                            'order' => $faq->order,
                        ];
                    })
                    ->values(),

                'banners' => $banners
                    ->map(function ($banner) {
                        return [
                            'id' => $banner->id,
                            'title' => $banner->title,
                            'description' => $banner->description,
                            'image_url' => $this->fileUrl(
                                $banner->image_path
                            ),
                            'link' => $banner->link,
                            'order' => $banner->order,
                        ];
                    })
                    ->values(),

                'sliders' => $sliders
                    ->map(function ($slider) {
                        return [
                            'id' => $slider->id,
                            'title' => $slider->title,
                            'subtitle' => $slider->subtitle,
                            'image_url' => $this->fileUrl(
                                $slider->image_path
                            ),
                            'button_text' => $slider->button_text,
                            'button_url' => $slider->button_url,
                            'order' => $slider->order,
                        ];
                    })
                    ->values(),

                'most_requested_topic' => $mostRequestedTopic
                    ? [
                        'subject' => $mostRequestedTopic->subject,
                        'requests_count' => (int) $mostRequestedTopic
                            ->total,
                    ]
                    : null,

                'stats' => [
                    'topics_count' => Topic::query()
                        ->active()
                        ->whereNull('parent_id')
                        ->count(),

                    'subjects_count' => Subject::query()
                        ->active()
                        ->count(),

                    'questions_count' => $vivaQuestionsCount
                        + $examQuestionsCount,

                    'students_count' => User::role('user')->count(),
                ],
            ],
        ]);
    }

    /**
     * Convert local storage paths or external links
     * into a usable URL.
     */
    private function fileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
