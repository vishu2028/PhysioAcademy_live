<?php

namespace Tests\Feature;

use App\Models\Doubt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDoubtResponseEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_doubt_response_form_uses_the_topics_ckeditor_toolbar(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.doubts.index'))
            ->assertOk()
            ->assertSee('https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js')
            ->assertSee("'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor'", false)
            ->assertSee("'blockQuote', 'imageUpload', 'insertTable', 'mediaEmbed'", false)
            ->assertSee('new TextDecoder().decode(Uint8Array.from(atob(value)', false)
            ->assertSee('pendingResponseHtml = decodeUtf8(button.dataset.answer)', false)
            ->assertSee('Cancel')
            ->assertSee('Save Response')
            ->assertDontSee('data-command="bold"', false);
    }

    public function test_formatted_response_is_saved_and_rendered_without_unsafe_html(): void
    {
        Notification::fake();
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = User::factory()->create();
        $doubt = Doubt::create([
            'user_id' => $student->id,
            'topic' => 'Cell physiology',
            'message' => 'Please explain the cell membrane.',
            'status' => Doubt::STATUS_PENDING,
        ]);

        $answer = <<<'HTML'
            <h2 style="color: rgb(20, 40, 60)">Cell membrane</h2>
            <p style="font-size: 18px; text-align: center"><strong>Key point</strong></p>
            <figure class="image"><img src="/storage/uploads/topics/cell.png" alt="Cell diagram" onerror="alert(1)"></figure>
            <table><tbody><tr><th>Part</th><td>Function</td></tr></tbody></table>
            <p><strong>Read <a href="https://example.test/read?part=1&amp;lang=hi">here →</a></strong> — हिन्दी ✓</p>
            <a href="javascript:alert(1)">Unsafe link</a>
            <script>alert(1)</script>
            <iframe src="https://attacker.example/embed/video"></iframe>
            <figure class="media"><iframe src="https://www.youtube.com/embed/abc_123"></iframe></figure>
            HTML;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.doubts.update', $doubt), [
                'status' => Doubt::STATUS_ANSWERED,
                'answer' => $answer,
            ])
            ->assertRedirect();

        $savedAnswer = $doubt->fresh()->answer;
        $this->assertStringContainsString('<h2', $savedAnswer);
        $this->assertStringContainsString('<img', $savedAnswer);
        $this->assertStringContainsString('<table', $savedAnswer);
        $this->assertStringContainsString('href="https://example.test/read?part=1&amp;lang=hi"', $savedAnswer);
        $this->assertStringContainsString('here →', $savedAnswer);
        $this->assertStringContainsString('— हिन्दी ✓', $savedAnswer);
        $this->assertStringContainsString('https://www.youtube.com/embed/abc_123', $savedAnswer);
        $this->assertStringNotContainsString('onerror', $savedAnswer);
        $this->assertStringNotContainsString('javascript:', $savedAnswer);
        $this->assertStringNotContainsString('<script', $savedAnswer);
        $this->assertStringNotContainsString('attacker.example', $savedAnswer);

        $adminResponse = $this->actingAs($admin, 'admin')
            ->get(route('admin.doubts.index'))
            ->assertOk();
        $this->assertSame(
            1,
            preg_match('/data-mode="edit"[^>]*data-answer="([^"]*)"/', $adminResponse->getContent(), $matches)
        );
        $reopenedAnswer = base64_decode($matches[1], true);
        $this->assertNotFalse($reopenedAnswer);
        $this->assertStringContainsString('https://example.test/read?part=1&amp;lang=hi', $reopenedAnswer);
        $this->assertStringContainsString('here →', $reopenedAnswer);
        $this->assertStringContainsString('— हिन्दी ✓', $reopenedAnswer);

        $this->actingAs($student)
            ->get(route('doubts.mine'))
            ->assertOk()
            ->assertSee('<h2', false)
            ->assertSee('<table', false)
            ->assertSee('<img', false)
            ->assertSee('here →', false)
            ->assertSee('हिन्दी ✓', false)
            ->assertSee('https://www.youtube.com/embed/abc_123', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('javascript:', false)
            ->assertDontSee('attacker.example', false);
    }
}
