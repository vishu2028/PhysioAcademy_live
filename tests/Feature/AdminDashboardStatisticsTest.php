<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\DoubtSessionBooking;
use App\Models\ExamAid;
use App\Models\GroupWebinarRequest;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Unit;
use App\Models\UnitTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_cards_show_database_counts_and_link_to_admin_listings(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create();
        $subject = Subject::create([
            'name' => 'Dashboard Subject',
            'slug' => 'dashboard-subject',
        ]);
        $unit = Unit::create([
            'subject_id' => $subject->id,
            'name' => 'Dashboard Unit',
            'slug' => 'dashboard-unit',
        ]);
        UnitTopic::create([
            'subject_id' => $subject->id,
            'unit_id' => $unit->id,
            'title' => 'Dashboard Unit Topic',
            'slug' => 'dashboard-unit-topic',
        ]);
        $academicYear = AcademicYear::create([
            'name' => 'Dashboard Year',
            'slug' => 'dashboard-year',
        ]);
        $activeTopic = Topic::create([
            'title' => 'Active Dashboard Topic',
            'slug' => 'active-dashboard-topic',
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'status' => true,
        ]);
        $inactiveTopic = Topic::create([
            'title' => 'Inactive Dashboard Topic',
            'slug' => 'inactive-dashboard-topic',
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'status' => false,
        ]);
        ExamAid::create([
            'subject_id' => $subject->id,
            'title' => 'Dashboard Exam Aid',
        ]);
        DoubtSessionBooking::create([
            'booking_reference' => 'DASHBOARD-BOOKING',
            'student_name' => 'Dashboard Student',
            'student_email' => 'dashboard-student@example.com',
            'phone' => '1234567890',
            'topic' => 'Dashboard topic',
            'doubt' => 'Dashboard doubt',
        ]);
        $groupRequest = GroupWebinarRequest::create([
            'reference' => 'DASHBOARD-WEBINAR',
            'organizer_name' => 'Dashboard Organizer',
            'organizer_email' => 'dashboard-organizer@example.com',
            'topic' => 'Dashboard group topic',
            'description' => 'Dashboard group description',
            'participant_count' => 2,
            'minimum_participants' => 2,
            'maximum_participants' => 10,
            'duration_minutes' => 60,
        ]);
        DB::table('group_webinar_participants')->insert([
            [
                'group_webinar_request_id' => $groupRequest->id,
                'email' => 'participant-one@example.com',
                'email_normalized' => 'participant-one@example.com',
            ],
            [
                'group_webinar_request_id' => $groupRequest->id,
                'email' => 'participant-two@example.com',
                'email_normalized' => 'participant-two@example.com',
            ],
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertViewHas('stats', function (array $stats): bool {
                return $stats['users'] === 2
                    && $stats['topics'] === 1
                    && $stats['topics_lms'] === 2
                    && $stats['exam_aids'] === 1
                    && $stats['doubt_sessions'] === 1
                    && $stats['group_webinars'] === 1;
            })
            ->assertSee(route('admin.users.index'), false)
            ->assertSee(route('admin.unit-topics.index'), false)
            ->assertSee(route('admin.topics.index'), false)
            ->assertSee('>Topics</p>', false)
            ->assertSee('LMS')
            ->assertSee(route('admin.exam-aids.index'), false)
            ->assertSee(route('admin.doubt-session-bookings.index'), false)
            ->assertSee(route('admin.group-webinars.index'), false)
            ->assertSee('Webinars')
            ->assertDontSee('Total Pages')
            ->assertDontSee('12% this month')
            ->assertViewHas('chartData.distribution', [
                'Active Topics' => 1,
                'Inactive Topics' => 1,
            ])
            ->assertSee('Active Topics')
            ->assertSee('Inactive Topics')
            ->assertSee('labels: ["Active Topics","Inactive Topics"]', false)
            ->assertSee('data: [1,1]', false);

        $groupWebinarCount = $response->viewData('stats')['group_webinars'];
        $this->assertSame(1, $groupWebinarCount);

        $groupRequest->delete();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertViewHas('stats.group_webinars', 0);

        $inactiveTopic->update(['status' => true]);
        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertViewHas('chartData.distribution', [
                'Active Topics' => 2,
                'Inactive Topics' => 0,
            ]);

        $activeTopic->delete();
        $inactiveTopic->delete();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertViewHas('chartData.distribution', [
                'Active Topics' => 0,
                'Inactive Topics' => 0,
            ]);
    }
}
