<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAidCategory;
use App\Models\ExamPrepCard;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamPrepController extends Controller
{
    /**
     * Section heading settings, stored in the `settings` table.
     */
    public const SETTING_DEFAULTS = [
        'exam_prep_enabled' => '1',
        'exam_prep_tag' => 'Exam Prep',
        'exam_prep_title' => 'Exam',
        'exam_prep_title_highlight' => 'Aid Center',
        'exam_prep_subtitle' => 'Comprehensive exam preparation toolkit for all physio exams',
    ];

    public static function settings(): array
    {
        $stored = Setting::whereIn('key', array_keys(self::SETTING_DEFAULTS))
            ->pluck('value', 'key');

        $settings = [];

        foreach (self::SETTING_DEFAULTS as $key => $default) {
            $settings[$key] = $stored->has($key) ? $stored[$key] : $default;
        }

        $settings['exam_prep_enabled'] = (bool) (int) $settings['exam_prep_enabled'];

        return $settings;
    }

    public function index()
    {
        $settings = self::settings();
        $cards = ExamPrepCard::with('category')->ordered()->get();

        return view('admin.exam-prep.index', compact('settings', 'cards'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'exam_prep_tag' => ['nullable', 'string', 'max:100'],
            'exam_prep_title' => ['nullable', 'string', 'max:150'],
            'exam_prep_title_highlight' => ['nullable', 'string', 'max:150'],
            'exam_prep_subtitle' => ['nullable', 'string', 'max:500'],
        ]);

        $values = array_merge($validated, [
            'exam_prep_enabled' => $request->boolean('exam_prep_enabled') ? '1' : '0',
        ]);

        foreach (self::SETTING_DEFAULTS as $key => $default) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) ($values[$key] ?? ''),
                    'group' => 'exam_prep',
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'type' => $key === 'exam_prep_enabled' ? 'boolean' : 'text',
                ]
            );
        }

        return redirect()
            ->route('admin.exam-prep.index')
            ->with('success', 'Exam Prep section updated successfully.');
    }

    public function create()
    {
        return view('admin.exam-prep.create', $this->formData());
    }

    public function store(Request $request)
    {
        ExamPrepCard::create($this->validated($request));

        return redirect()
            ->route('admin.exam-prep.index')
            ->with('success', 'Card created successfully.');
    }

    public function edit(ExamPrepCard $examPrepCard)
    {
        return view('admin.exam-prep.edit', array_merge(
            $this->formData(),
            ['card' => $examPrepCard]
        ));
    }

    public function update(Request $request, ExamPrepCard $examPrepCard)
    {
        $examPrepCard->update($this->validated($request));

        return redirect()
            ->route('admin.exam-prep.index')
            ->with('success', 'Card updated successfully.');
    }

    public function destroy(ExamPrepCard $examPrepCard)
    {
        $examPrepCard->delete();

        return redirect()
            ->route('admin.exam-prep.index')
            ->with('success', 'Card deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'categories' => ExamAidCategory::ordered()->get(),
            'icons' => ExamPrepCard::ICONS,
        ];
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['required', Rule::in(ExamPrepCard::ICONS)],
            'button_text' => ['required', 'string', 'max:100'],
            'exam_aid_category_id' => ['nullable', 'exists:exam_aid_categories,id'],
            'custom_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/)/i'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'custom_url.regex' => 'The link must start with http://, https:// or /.',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_large'] = $request->boolean('is_large');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
