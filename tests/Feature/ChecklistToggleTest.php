<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistToggleTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'toggle@example.com'): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom',
            'bride_name' => 'Bride',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);

        return User::factory()->create([
            'name' => 'Samuel',
            'email' => $email,
            'wedding_id' => $wedding->id,
        ]);
    }

    public function test_toggle_returns_json_without_redirect(): void
    {
        $user = $this->makeUser();
        $item = $user->wedding->checklists()->create([
            'title' => 'Booking Catering',
            'category' => 'Catering',
            'priority' => 'High',
            'status' => 'Todo',
        ]);

        $res = $this->actingAs($user)->postJson(route('checklists.toggle', $item));

        $res->assertOk()->assertJson([
            'status' => 'Done',
            'total' => 1,
            'doneCount' => 1,
            'todoCount' => 0,
        ]);
        $this->assertSame('Done', $item->fresh()->status);
    }

    public function test_toggle_back_to_todo(): void
    {
        $user = $this->makeUser('toggle2@example.com');
        $item = $user->wedding->checklists()->create([
            'title' => 'Booking Venue',
            'category' => 'Venue',
            'priority' => 'High',
            'status' => 'Done',
        ]);

        $res = $this->actingAs($user)->postJson(route('checklists.toggle', $item));

        $res->assertOk()->assertJson(['status' => 'Todo']);
        $this->assertSame('Todo', $item->fresh()->status);
    }

    public function test_toggle_plain_post_still_redirects(): void
    {
        $user = $this->makeUser('toggle3@example.com');
        $item = $user->wedding->checklists()->create([
            'title' => 'Booking MUA',
            'category' => 'MUA',
            'priority' => 'Medium',
            'status' => 'Todo',
        ]);

        $this->actingAs($user)->post(route('checklists.toggle', $item))
            ->assertRedirect();

        $this->assertSame('Done', $item->fresh()->status);
    }
}
