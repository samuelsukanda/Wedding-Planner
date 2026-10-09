<?php

namespace Tests\Feature;

use App\Models\RundownEvent;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RundownTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_saves_time_range_without_sort_order(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Diki',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)->post(route('rundowns.store'), [
            'start_time' => '10:00',
            'end_time' => '11:30',
            'activity' => 'Akad',
            'pic' => 'Panitia',
        ])->assertRedirect(route('rundowns.index'));

        $this->assertDatabaseHas('rundown_events', [
            'wedding_id' => $wedding->id,
            'time' => '10:00 - 11:30',
            'activity' => 'Akad',
        ]);
    }

    public function test_index_orders_events_by_start_time_including_legacy_ranges(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Diki',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        foreach ([
            ['time' => '10:00 - 11:00', 'activity' => 'Pagi'],
            ['time' => '09.00 - 09.30', 'activity' => 'Pembukaan'],
            ['time' => '10:00 - 10:30', 'activity' => 'Sambutan'],
        ] as $event) {
            RundownEvent::create(array_merge($event, [
                'wedding_id' => $wedding->id,
                'pic' => 'Panitia',
            ]));
        }

        $this->actingAs($user)->get(route('rundowns.index'))
            ->assertOk()
            ->assertSeeInOrder(['Pembukaan', 'Pagi', 'Sambutan']);
    }

    public function test_store_rejects_missing_or_invalid_time_values(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Diki',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);

        $this->actingAs($user)->post(route('rundowns.store'), [
            'start_time' => '25:90',
            'activity' => 'Akad',
            'pic' => 'Panitia',
        ])->assertSessionHasErrors(['start_time', 'end_time']);

        $this->assertSame(0, $wedding->rundownEvents()->count());
    }

    public function test_update_saves_time_range(): void
    {
        $wedding = Wedding::create([
            'groom_name' => 'Diki',
            'bride_name' => 'Nabila',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
        ]);
        $user = User::factory()->create(['wedding_id' => $wedding->id]);
        $event = $wedding->rundownEvents()->create([
            'time' => '09.00 - 10.00',
            'activity' => 'Akad',
            'pic' => 'Panitia',
        ]);

        $this->actingAs($user)->put(route('rundowns.update', $event), [
            'start_time' => '13:15',
            'end_time' => '14:00',
            'activity' => 'Akad',
            'pic' => 'Panitia',
        ])->assertRedirect(route('rundowns.index'));

        $this->assertDatabaseHas('rundown_events', [
            'id' => $event->id,
            'time' => '13:15 - 14:00',
        ]);
    }
}
