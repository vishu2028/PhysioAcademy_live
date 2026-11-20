<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoMetadata
{
    public function synchronize(Model $model): void
    {
        $title = $this->contentTitle($model);
        $description = $this->contentDescription($model, $title);

        $model->setAttribute('seo_title', Str::limit($title, 255, ''));
        if (class_basename($model) === 'Page') {
            $model->setAttribute('meta_title', Str::limit($title, 255, ''));
        } else {
            $model->setAttribute('meta_description', Str::limit($description, 500, ''));
        }
        $model->setAttribute('og_title', Str::limit($title, 255, ''));
        $model->setAttribute('og_description', Str::limit($description, 500, ''));

        $slug = $model->getAttribute('slug');
        if (blank($model->getAttribute('seo_slug')) && is_string($slug) && trim($slug) !== '') {
            $model->setAttribute('seo_slug', $slug);
        }

        if (blank($model->getAttribute('og_image'))) {
            $image = $this->contentImage($model);

            if ($image !== null) {
                $model->setAttribute('og_image', $image);
            }
        }
    }

    public function for(
        Model $model,
        string $fallbackTitle,
        ?string $canonicalUrl = null,
        bool $isPrivate = false
    ): array {
        $title = $this->contentTitle($model, $fallbackTitle);
        $description = $this->contentDescription($model, $title);
        $canonical = $canonicalUrl ?: $this->configuredUrl(request()->getPathInfo());
        $indexable = ! $isPrivate
            && $this->isEligibleForIndexing($model)
            && $model->getAttribute('robots_index') !== false;
        $followable = $indexable && $model->getAttribute('robots_follow') !== false;
        $image = $this->imageUrl($this->contentImage($model));

        $structuredDataType = match (class_basename($model)) {
            'Topic', 'LearningMaterial' => 'LearningResource',
            'Webinar' => 'Event',
            'AcademicYear', 'ExamAidCategory' => 'CollectionPage',
            default => 'WebPage',
        };
        $openGraphType = in_array(class_basename($model), ['Topic', 'Webinar'], true)
            ? 'article'
            : 'website';

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => ($indexable ? 'index' : 'noindex') . ', ' . ($followable ? 'follow' : 'nofollow'),
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $image,
            'og_type' => $openGraphType,
            'twitter_card' => $image ? 'summary_large_image' : 'summary',
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => $structuredDataType,
                'name' => $title,
                'description' => $description,
                'url' => $canonical,
            ],
        ];
    }

    public function forPage(
        string $title,
        ?string $description,
        ?string $canonicalUrl = null,
        bool $isPrivate = false
    ): array {
        $title = $this->plainText($title) ?: get_setting('site_name', 'PhysioSource');
        $description = $this->plainText($description);
        if ($description === '' || $description === $this->plainText($title)) {
            $description = "Explore {$title} on PhysioSource.";
        }
        $description = $this->excerpt($description);
        $canonical = $canonicalUrl ?: $this->configuredUrl(request()->getPathInfo());

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $isPrivate ? 'noindex, nofollow' : 'index, follow',
            'og_title' => $title,
            'og_description' => $description,
            'og_image' => $this->imageUrl(null),
            'og_type' => 'website',
            'twitter_card' => get_setting('site_og_image') || get_setting('site_logo')
                ? 'summary_large_image'
                : 'summary',
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'name' => $title,
                'description' => $description,
                'url' => $canonical,
            ],
        ];
    }

    private function contentTitle(Model $model, ?string $fallback = null): string
    {
        foreach (['title', 'name'] as $field) {
            $title = $this->plainText($model->getAttribute($field));

            if ($title !== '') {
                return Str::limit($title, 255, '');
            }
        }

        return $this->plainText($fallback) ?: get_setting('site_name', 'PhysioSource');
    }

    private function contentDescription(Model $model, string $title): string
    {
        foreach ([
            'description',
            'content',
            'bottom_line',
            'viva_question',
            'exam_question',
            'learning_objectives',
        ] as $field) {
            $description = $this->plainText($model->getAttribute($field));

            if ($description !== '' && $description !== $title) {
                return $this->excerpt($description);
            }
        }

        return $this->excerpt("Explore {$title} on PhysioSource.");
    }

    private function contentImage(Model $model): ?string
    {
        foreach (['image', 'image_path', 'thumbnail', 'thumbnail_path', 'cover_image', 'banner_image'] as $field) {
            $image = $model->getAttribute($field);

            if (is_string($image) && trim($image) !== '') {
                return trim($image);
            }
        }

        $content = $model->getAttribute('content');
        if (is_string($content) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    public function imageUrl(?string $image): ?string
    {
        $image ??= get_setting('site_og_image') ?: get_setting('site_logo');

        if (! $image) {
            return null;
        }

        $siteUrl = rtrim((string) config('app.url'), '/');
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return filter_var($image, FILTER_VALIDATE_URL) !== false ? $image : null;
        }

        if (Str::startsWith($image, '//')) {
            if (! filter_var('https:' . $image, FILTER_VALIDATE_URL)) {
                return null;
            }

            return (parse_url($siteUrl, PHP_URL_SCHEME) ?: 'https') . ':' . $image;
        }

        if (Str::startsWith($image, '/')) {
            return $siteUrl . $image;
        }

        $imagePath = ltrim($image, '/');

        return $siteUrl . '/' . (Str::startsWith($imagePath, 'storage/')
            ? $imagePath
            : 'storage/' . $imagePath);
    }

    private function configuredUrl(string $path): string
    {
        return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
    }

    private function excerpt(string $text): string
    {
        if (mb_strlen($text) <= 160) {
            return $text;
        }

        $prefix = mb_substr($text, 0, 157);
        $lastSpace = mb_strrpos($prefix, ' ');

        if ($lastSpace !== false && $lastSpace > 0) {
            $prefix = mb_substr($prefix, 0, $lastSpace);
        }

        return rtrim($prefix) . '...';
    }

    private function isEligibleForIndexing(Model $model): bool
    {
        return match (class_basename($model)) {
            'Topic' => (bool) $model->getAttribute('status')
                && ! (bool) $model->getAttribute('is_protected')
                && $this->relatedStatus($model, 'subject', 'status')
                && $this->relatedStatus($model, 'academicYear', 'status')
                && $this->relatedStatus($model, 'unitTopic', 'status')
                && $this->relatedStatus($model, 'unitTopic.unit', 'is_active'),
            'Page' => (bool) $model->getAttribute('status')
                && ! (bool) $model->getAttribute('is_protected'),
            'AcademicYear', 'Subject' => (bool) $model->getAttribute('status'),
            'Unit' => (bool) $model->getAttribute('is_active')
                && $this->relatedStatus($model, 'subject', 'status'),
            'ExamAid' => (bool) $model->getAttribute('status')
                && $this->relatedStatus($model, 'subject', 'status'),
            'ExamAidCategory' => (bool) $model->getAttribute('is_active'),
            'Webinar' => $model->getAttribute('status') === 'published',
            'LearningMaterial' => $this->learningMaterialIsPublic($model),
            'ExamAidMaterial' => $this->examAidMaterialIsPublic($model),
            default => true,
        };
    }

    private function relatedStatus(Model $model, string $relation, string $attribute): bool
    {
        $related = $model;

        foreach (explode('.', $relation) as $relationName) {
            if (! method_exists($related, $relationName)) {
                return false;
            }

            $related->loadMissing($relationName);
            $related = $related->getRelationValue($relationName);
            if (! $related instanceof Model) {
                return false;
            }
        }

        return (bool) $related->getAttribute($attribute);
    }

    private function learningMaterialIsPublic(Model $model): bool
    {
        if ($model->getAttribute('topic_id')) {
            $topic = $model->getRelationValue('topic');

            return $topic instanceof Model
                && $this->isEligibleForIndexing($topic);
        }

        $category = $model->getRelationValue('category');

        return $category instanceof Model
            && (bool) $category->getAttribute('is_active');
    }

    private function examAidMaterialIsPublic(Model $model): bool
    {
        $examAid = $model->getRelationValue('examAid');

        return $examAid instanceof Model
            && (bool) $examAid->getAttribute('status')
            && $this->relatedStatus($examAid, 'subject', 'status');
    }

    private function plainText(?string $value): string
    {
        $value = preg_replace(
            '/<\/?(?:address|article|blockquote|br|div|dl|dt|dd|h[1-6]|li|ol|p|pre|section|table|tr|ul)\b[^>]*>/i',
            ' ',
            $value ?? ''
        );

        return trim(preg_replace(
            '/\s+/',
            ' ',
            html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5)
        ));
    }
}
