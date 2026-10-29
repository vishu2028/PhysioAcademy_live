<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public static function sanitizeHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $html = preg_replace(
            '/<(script|style|iframe|object|embed|form)\b[^>]*>.*?<\/\1>/is',
            '',
            $html
        ) ?? '';
        $html = strip_tags(
            $html,
            '<p><br><strong><b><em><i><u><s><del><span><div><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a>'
        );
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace_callback(
            '/\s+style\s*=\s*(["\'])(.*?)\1/is',
            static function (array $match): string {
                $declarations = [];

                foreach (explode(';', $match[2]) as $declaration) {
                    [$property, $value] = array_pad(explode(':', $declaration, 2), 2, '');
                    $property = strtolower(trim($property));
                    $value = trim($value);

                    if (
                        in_array($property, [
                            'color',
                            'background-color',
                            'font-size',
                            'font-family',
                            'text-align',
                        ], true)
                        && $value !== ''
                        && ! preg_match('/(expression|javascript|vbscript|url\s*\()/i', $value)
                    ) {
                        $declarations[] = $property . ':' . e($value);
                    }
                }

                return $declarations
                    ? ' style="' . implode(';', $declarations) . '"'
                    : '';
            },
            $html
        ) ?? '';
        $html = preg_replace_callback(
            '/\s+(href|src)\s*=\s*(["\'])(.*?)\2/is',
            static function (array $match): string {
                $value = trim($match[3]);

                if (preg_match('/^(javascript|data|vbscript):/i', $value)) {
                    return '';
                }

                return ' ' . $match[1] . '=' . $match[2] . e($value) . $match[2];
            },
            $html
        ) ?? '';

        return $html;
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
