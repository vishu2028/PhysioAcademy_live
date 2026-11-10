<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\TopicRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTopicRequestUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_request_without_changing_existing_published_topic_link(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = User::factory()->create();
        $subject = Subject::create([
            'name' => 'Topic Request Subject',
            'slug' => 'topic-request-subject',
        ]);
        $academicYear = AcademicYear::create([
            'name' => 'Topic Request Year',
            'slug' => 'topic-request-year',
        ]);
        $topic = Topic::create([
            'title' => 'Existing Published Topic',
            'slug' => 'existing-published-topic',
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'status' => true,
        ]);
        $topicRequest = TopicRequest::create([
            'user_id' => $student->id,
            'topic_name' => 'Requested Topic',
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'topic_id' => $topic->id,
            'description' => 'Please add this topic.',
            'submission_token' => (string) str()->uuid(),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.topic-requests.index'))
            ->assertOk()
            ->assertSee('Pending')
            ->assertSee('Topic Link / Response')
            ->assertSee('Save Update')
            ->assertSee('View published topic')
            ->assertDontSee('No published topic linked')
            ->assertDontSee('name="topic_id"', false);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.topic-requests.update', $topicRequest), [
                'status' => TopicRequest::STATUS_RESOLVED,
                'topic_link' => 'https://example.test/requested-topic',
            ])
            ->assertRedirect(route('admin.topic-requests.index'));

        $this->assertDatabaseHas('topic_requests', [
            'id' => $topicRequest->id,
            'status' => TopicRequest::STATUS_RESOLVED,
            'topic_link' => 'https://example.test/requested-topic',
            'topic_id' => $topic->id,
        ]);
    }
}
