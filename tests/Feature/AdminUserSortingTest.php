<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_joined_date_sorting_uses_database_timestamps_and_stable_ids_across_pages(): void
    {
        $admin = $this->createAdmin();
        $users = collect();

        for ($index = 0; $index < 23; $index++) {
            $user = User::factory()->create([
                'name' => sprintf('Joined Cohort User %02d', $index),
                'email' => sprintf('joined-cohort-%02d@example.test', $index),
            ]);
            $registeredAt = now()->startOfDay()->addDays(intdiv($index, 6));
            $user->forceFill([
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ])->save();
            $users->push($user);
        }

        $expectedAscending = $users
            ->sort(fn (User $left, User $right) => [$left->created_at->timestamp, $left->id] <=> [$right->created_at->timestamp, $right->id])
            ->pluck('id')
            ->values()
            ->all();
        $expectedDescending = $users
            ->sort(fn (User $left, User $right) => [$right->created_at->timestamp, $right->id] <=> [$left->created_at->timestamp, $left->id])
            ->pluck('id')
            ->values()
            ->all();

        $this->actingAs($admin, 'admin');

        foreach (['asc' => $expectedAscending, 'desc' => $expectedDescending] as $direction => $expectedIds) {
            for ($page = 1; $page <= 3; $page++) {
                $response = $this->get(route('admin.users.index', [
                    'search' => 'Joined Cohort',
                    'direction' => $direction,
                    'page' => $page,
                ]))->assertOk();

                $pageIds = array_slice($expectedIds, ($page - 1) * 10, 10);
                $this->assertSame(
                    $pageIds,
                    $response->viewData('users')->getCollection()->pluck('id')->all(),
                    "Unexpected {$direction} Joined Date order on page {$page}."
                );
            }
        }
    }

    public function test_joined_date_sorting_and_search_preserve_each_other_and_toggle_from_heading(): void
    {
        $admin = $this->createAdmin();
        $matchingUser = User::factory()->create([
            'name' => 'Matching Joined Cohort User',
            'email' => 'matching-joined-cohort@example.test',
            'created_at' => '2022-01-01 10:00:00',
        ]);
        User::factory()->create([
            'name' => 'Unrelated User',
            'email' => 'unrelated@example.test',
            'created_at' => '2020-01-01 10:00:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.users.index', [
            'search' => 'Matching Joined Cohort',
            'direction' => 'asc',
        ]))->assertOk();

        $this->assertSame(
            [$matchingUser->id],
            $response->viewData('users')->getCollection()->pluck('id')->all()
        );
        $toggleUrl = route('admin.users.index', [
                'search' => 'Matching Joined Cohort',
                'direction' => 'desc',
            ]);
        $response->assertSee('name="search"', false)
            ->assertSee('value="Matching Joined Cohort"', false)
            ->assertSee('<option value="asc" selected', false)
            ->assertSee(str_replace('&', '&amp;', $toggleUrl), false);
    }

    private function createAdmin(): User
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create([
            'name' => 'User Management Admin',
            'email' => 'user-management-admin@example.test',
            'created_at' => '2019-01-01 00:00:00',
        ]);
        $admin->assignRole('admin');

        return $admin;
    }
}
