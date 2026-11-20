<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ExamAidCategory;
use App\Models\Page;
use App\Models\Topic;
use App\Models\Webinar;
use App\Services\SeoMetadata;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function __invoke(SeoMetadata $seoMetadata): Response
    {
        $entries = [];
        $seenCanonicalUrls = [];
        $siteUrl = rtrim(config('app.url'), '/');
        $siteHost = parse_url($siteUrl, PHP_URL_HOST);

        $addEntry = function ($model, string $title, string $routeName, array $parameters = [], bool $private = false) use (&$entries, &$seenCanonicalUrls, $seoMetadata, $siteUrl, $siteHost): void {
            $metadata = $seoMetadata->for(
                $model,
                $title,
                $siteUrl . route($routeName, $parameters, false),
                $private
            );

            if (str_contains($metadata['robots'], 'noindex')) {
                return;
            }

            $canonical = $metadata['canonical'];
            if (! $canonical || parse_url($canonical, PHP_URL_HOST) !== $siteHost || isset($seenCanonicalUrls[$canonical])) {
                return;
            }

            $seenCanonicalUrls[$canonical] = true;
            $entries[] = [
                'url' => $canonical,
                'lastmod' => $model->updated_at?->toAtomString(),
            ];
        };

        foreach ([
            'home',
            'topics.index',
            'topics.year',
            'webinars.index',
            'faq',
        ] as $routeName) {
            $url = $siteUrl . route($routeName, [], false);
            if (! isset($seenCanonicalUrls[$url])) {
                $seenCanonicalUrls[$url] = true;
                $entries[] = ['url' => $url, 'lastmod' => null];
            }
        }

        foreach ([
            'privacy-policy' => 'support.privacy-policy',
            'terms-and-conditions' => 'support.terms',
        ] as $slug => $routeName) {
            $page = Page::query()->where('slug', $slug)->first();

            if ($page?->status) {
                $addEntry(
                    $page,
                    $page->title,
                    $routeName,
                    [],
                    $page->is_protected
                );
            } elseif (! $page) {
                $url = $siteUrl . route($routeName, [], false);
                if (! isset($seenCanonicalUrls[$url])) {
                    $seenCanonicalUrls[$url] = true;
                    $entries[] = ['url' => $url, 'lastmod' => null];
                }
            }
        }

        foreach ([
            'about-us' => 'about',
            'contact-us' => 'contact',
            'exam-aid' => 'exam-aid',
        ] as $pageSlug => $routeName) {
            $page = Page::query()->where('slug', $pageSlug)->active()->first();

            if ($page) {
                $addEntry(
                    $page,
                    $page->title,
                    $routeName,
                    [],
                    $page->is_protected
                );
            }
        }

        Topic::query()
            ->active()
            ->where('is_protected', false)
            ->with(['subject', 'academicYear', 'unitTopic.unit'])
            ->whereHas('subject', fn ($query) => $query->where('status', true))
            ->whereHas('academicYear', fn ($query) => $query->where('status', true))
            ->whereHas('unitTopic', function ($query) {
                $query->where('status', true)
                    ->whereHas('unit', fn ($unit) => $unit->where('is_active', true));
            })
            ->orderBy('id')
            ->get()
            ->each(fn (Topic $topic) => $addEntry(
                $topic,
                $topic->title,
                'topics.show',
                ['slug' => $topic->seo_slug ?: $topic->slug],
                $topic->is_protected
            ));

        AcademicYear::query()
            ->active()
            ->orderBy('id')
            ->get()
            ->each(fn (AcademicYear $year) => $addEntry(
                $year,
                $year->name,
                'topics.year',
                ['year' => $year->seo_slug ?: $year->slug]
            ));

        ExamAidCategory::query()
            ->active()
            ->orderBy('id')
            ->get()
            ->each(fn (ExamAidCategory $category) => $addEntry(
                $category,
                $category->name,
                'exam-aid.category',
                ['slug' => $category->seo_slug ?: $category->slug]
            ));

        $reservedSlugs = [
            'about-us',
            'contact-us',
            'bookmarks',
            'exam-aid',
            'home',
            'faq',
            'privacy-policy',
            'terms-and-conditions',
        ];

        Page::query()
            ->active()
            ->where('is_protected', false)
            ->whereNotIn('slug', $reservedSlugs)
            ->orderBy('id')
            ->get()
            ->each(fn (Page $page) => $addEntry(
                $page,
                $page->title,
                'page.show',
                ['slug' => $page->seo_slug ?: $page->slug]
            ));

        Webinar::query()
            ->where('status', 'published')
            ->orderBy('id')
            ->get()
            ->each(fn (Webinar $webinar) => $addEntry(
                $webinar,
                $webinar->title,
                'webinars.show',
                ['webinar' => $webinar->seo_slug ?: $webinar->slug]
            ));

        return response()
            ->view('sitemap', ['entries' => new Collection($entries)])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
