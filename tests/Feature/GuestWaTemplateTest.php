<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestWaTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email = 'wa@example.com'): User
    {
        $wedding = Wedding::create([
            'groom_name' => 'Groom Full',
            'bride_name' => 'Bride Full',
            'wedding_date' => '2027-10-07',
            'total_budget' => 100000000,
            'location' => 'Jakarta',
        ]);

        return User::factory()->create([
            'name' => 'Samuel',
            'email' => $email,
            'wedding_id' => $wedding->id,
        ]);
    }

    private function makeGuest(User $user): Guest
    {
        return Guest::create([
            'wedding_id' => $user->wedding_id,
            'name' => 'Budi',
            'title' => 'Bpk.',
            'phone' => '08123456789',
            'attendance_status' => 'Pending',
            'guest_count' => 2,
        ]);
    }

    public function test_custom_template_is_used_in_wa_link(): void
    {
        $user = $this->makeUser();
        $guest = $this->makeGuest($user);

        $this->actingAs($user)->put(route('guests.wa-template.update'), [
            'wa_template' => 'Halo {nama}, datang ya ke nikahan {mempelai}!',
        ])->assertRedirect(route('guests.wa-all'));

        $this->assertSame(
            'Halo {nama}, datang ya ke nikahan {mempelai}!',
            $user->wedding->fresh()->wa_template
        );

        $res = $this->actingAs($user)->get(route('guests.wa-all'))->assertOk();
        $this->assertStringContainsString(
            rawurlencode('Halo Bpk. Budi, datang ya ke nikahan Groom & Bride!'),
            $res->getContent()
        );
    }

    public function test_send_wa_uses_custom_template(): void
    {
        $user = $this->makeUser('wa2@example.com');
        $guest = $this->makeGuest($user);
        $user->wedding->update(['wa_template' => 'Hai {nama}, {tanggal} di {tempat}. Info: {link} jam {waktu}.']);

        $res = $this->actingAs($user)->get(route('guests.wa', $guest));
        $target = $res->headers->get('Location');

        $this->assertStringStartsWith('https://api.whatsapp.com/send?phone=628123456789&text=', $target);
        parse_str(parse_url($target, PHP_URL_QUERY), $q);
        $this->assertSame(
            'Hai Bpk. Budi, 07 October 2027 di Jakarta. Info: weddingplanner.web.id jam 17:00.',
            $q['text']
        );
    }

    public function test_empty_template_falls_back_to_default(): void
    {
        $user = $this->makeUser('wa3@example.com');
        $this->makeGuest($user);
        $user->wedding->update(['wa_template' => 'Kustom']);

        $this->actingAs($user)->put(route('guests.wa-template.update'), [
            'wa_template' => '   ',
        ])->assertRedirect(route('guests.wa-all'));

        $this->assertNull($user->wedding->fresh()->wa_template);

        $res = $this->actingAs($user)->get(route('guests.wa-all'))->assertOk();
        $this->assertStringContainsString('Pesan: bawaan', $res->getContent());
        $this->assertStringContainsString('Kepada Yth.', $res->getContent());
    }

    /**
     * Token baru {nama tamu} dirender jadi nama lengkap tamu, dan token lama
     * {nama} tetap didukung supaya template kustom yang tersimpan tidak rusak.
     */
    public function test_nama_tamu_token_is_rendered_and_legacy_nama_still_works(): void
    {
        $user = $this->makeUser('wa4@example.com');
        $guest = $this->makeGuest($user);
        $user->wedding->update(['wa_template' => 'Halo {nama tamu}! Alias {nama}.']);

        $res = $this->actingAs($user)->get(route('guests.wa', $guest));
        $target = $res->headers->get('Location');

        parse_str(parse_url($target, PHP_URL_QUERY), $q);
        $this->assertSame('Halo Bpk. Budi! Alias Bpk. Budi.', $q['text']);
    }
}
