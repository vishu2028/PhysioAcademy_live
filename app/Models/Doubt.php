<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doubt extends Model
{
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'subject_id',
        'topic',
        'message',
        'answer',
        'status',
        'answered_by',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_REJECTED = 'rejected';

    public function messages(): HasMany
    {
        return $this->hasMany(DoubtMessage::class)->orderBy('created_at')->orderBy('id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(DoubtLike::class);
    }

    public static function sanitizeHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previousErrorHandling = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="doubt-html-root">' . $html . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        if (! $loaded) {
            throw new \RuntimeException('Unable to parse doubt response HTML.');
        }

        $root = $document->getElementsByTagName('div')->item(0);

        if (! $root) {
            throw new \RuntimeException('Unable to locate parsed doubt response HTML.');
        }

        self::sanitizeChildren($root, $document);

        $safeHtml = '';
        foreach ($root->childNodes as $child) {
            $safeHtml .= $document->saveHTML($child);
        }

        return $safeHtml;
    }

    private static function sanitizeChildren(\DOMNode $parent, \DOMDocument $document): void
    {
        $allowedTags = [
            'a', 'b', 'blockquote', 'br', 'caption', 'col', 'colgroup', 'del', 'div',
            'em', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'hr', 'i', 'img', 'iframe', 'li', 'ol', 'p', 's', 'span', 'strong',
            'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul',
        ];
        $voidTags = ['br', 'col', 'hr', 'img'];

        foreach (iterator_to_array($parent->childNodes) as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if ($tag === 'iframe' && ! self::isAllowedMediaUrl($child->getAttribute('src'))) {
                $parent->removeChild($child);
                continue;
            }

            if (! in_array($tag, $allowedTags, true)) {
                if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'svg', 'math'], true)) {
                    $parent->removeChild($child);
                    continue;
                }

                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);
                continue;
            }

            self::sanitizeAttributes($child, $tag);

            if ($tag === 'iframe') {
                while ($child->firstChild) {
                    $child->removeChild($child->firstChild);
                }
                $child->setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation');
                $child->setAttribute('loading', 'lazy');
                $child->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
                $child->setAttribute('allowfullscreen', '');
            } elseif (! in_array($tag, $voidTags, true)) {
                self::sanitizeChildren($child, $document);
            }
        }
    }

    private static function sanitizeAttributes(\DOMElement $element, string $tag): void
    {
        $attributes = iterator_to_array($element->attributes);

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            $keep = match ($name) {
                'style' => self::sanitizeStyle($value) !== '',
                'class' => self::sanitizeClassNames($value) !== '',
                'href' => $tag === 'a' && self::isAllowedLinkUrl($value),
                'src' => in_array($tag, ['img', 'iframe'], true)
                    && ($tag === 'img' ? self::isAllowedImageUrl($value) : self::isAllowedMediaUrl($value)),
                'alt', 'title' => in_array($tag, ['a', 'img', 'iframe'], true),
                'target' => $tag === 'a' && $value === '_blank',
                'width', 'height' => in_array($tag, ['img', 'iframe', 'table', 'td', 'th'], true)
                    && preg_match('/^\d{1,4}%?$/', $value),
                'colspan', 'rowspan' => in_array($tag, ['td', 'th'], true)
                    && preg_match('/^\d{1,2}$/', $value),
                'scope' => $tag === 'th' && in_array($value, ['row', 'col', 'rowgroup', 'colgroup'], true),
                default => false,
            };

            if (! $keep) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'style') {
                $element->setAttribute('style', self::sanitizeStyle($value));
            } elseif ($name === 'class') {
                $classNames = self::sanitizeClassNames($value);
                if ($classNames === '') {
                    $element->removeAttribute('class');
                } else {
                    $element->setAttribute('class', $classNames);
                }
            } elseif ($name === 'href' && $value !== '') {
                $element->setAttribute('href', $value);
                if ($value[0] !== '#') {
                    $element->setAttribute('rel', 'noopener noreferrer');
                }
            } elseif ($name === 'src') {
                $element->setAttribute('src', $value);
            }
        }

        if ($tag === 'img' && $element->hasAttribute('src')) {
            $element->setAttribute('loading', 'lazy');
        }
    }

    private static function sanitizeStyle(string $style): string
    {
        $safeStyles = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $value = strtolower($value);

            $isValid = match ($property) {
                'color', 'background-color' => (bool) preg_match(
                    '/^(#[0-9a-f]{3,8}|[a-z]{3,20}|rgba?\([\d.%\s,]+\)|hsla?\([\d.%\s,deg]+\))$/i',
                    $value
                ),
                'font-size', 'width', 'height', 'margin-left', 'margin-right', 'margin-top', 'margin-bottom' => (bool) preg_match(
                    '/^(auto|[-+]?\d*\.?\d+(px|em|rem|%|vw|vh))$/',
                    $value
                ),
                'font-family' => (bool) preg_match('/^[a-z0-9 ,"\'-]{1,100}$/i', $value),
                'text-align' => in_array($value, ['left', 'right', 'center', 'justify', 'start', 'end'], true),
                default => false,
            };

            if ($isValid) {
                $safeStyles[] = $property . ':' . e($value);
            }
        }

        return implode(';', $safeStyles);
    }

    private static function sanitizeClassNames(string $classNames): string
    {
        $allowedClasses = [
            'image',
            'image-style-align-center',
            'image-style-align-left',
            'image-style-align-right',
            'image-style-side',
            'image_resized',
            'media',
            'table',
        ];

        return implode(' ', array_intersect(
            preg_split('/\s+/', trim($classNames), -1, PREG_SPLIT_NO_EMPTY) ?: [],
            $allowedClasses
        ));
    }

    private static function isAllowedLinkUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || preg_match('/[\x00-\x20\x7f]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '#')) {
            return (bool) preg_match('/^#[a-zA-Z][a-zA-Z0-9._:-]*$/', $url);
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if ($scheme === 'mailto') {
            return filter_var(substr($url, 7), FILTER_VALIDATE_EMAIL) !== false;
        }

        return in_array($scheme, ['http', 'https'], true)
            && ! empty($parts['host'])
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private static function isAllowedImageUrl(string $url): bool
    {
        return $url !== ''
            && ! preg_match('/[\x00-\x20]/', $url)
            && (str_starts_with($url, '/') && ! str_starts_with($url, '//')
                || preg_match('/^https?:/i', $url));
    }

    private static function isAllowedMediaUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';

        return in_array($host, [
            'youtube.com',
            'www.youtube.com',
            'youtube-nocookie.com',
            'www.youtube-nocookie.com',
            'player.vimeo.com',
        ], true) && preg_match('~^/(embed|video)/[a-zA-Z0-9_-]+/?$~', $path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }
}
