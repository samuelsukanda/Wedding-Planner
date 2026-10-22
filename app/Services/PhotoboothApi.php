<?php

namespace App\Services;

use App\Models\Wedding;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Klien API Photobooth.
 *
 * Satu tempat yang tahu cara bicara dengan Photobooth: base URL, header
 * API key per event, dan batas waktu. Dipakai controller menu Photobooth
 * maupun sinkron otomatis dari halaman admin.
 */
class PhotoboothApi
{
    public function for(Wedding $wedding): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.photobooth.url', 'https://photobooth.nupsera.my.id'), '/'))
            ->withHeaders([
                'X-Api-Key' => (string) $wedding->photoboothApiKey(),
                'Accept' => 'application/json',
            ])
            ->timeout(8);
    }

    /**
     * Kirim perubahan event. Hanya field yang ada di $payload yang dikirim,
     * jadi pengaturan yang tidak disentuh tidak ikut tertimpa.
     */
    public function updateEvent(Wedding $wedding, array $payload): Response
    {
        return $this->for($wedding)->put('/api/admin/event', $payload);
    }

    /**
     * Minta Photobooth membuat (atau memperbarui) event untuk pasangan ini.
     *
     * Endpoint ini dikunci server key, bukan api_key per event, karena api_key
     * memang belum ada saat event baru dibuat.
     */
    public function provision(array $payload): Response
    {
        return Http::baseUrl(rtrim(config('services.photobooth.url', 'https://photobooth.nupsera.my.id'), '/'))
            ->withHeaders([
                'X-Server-Key' => (string) config('services.photobooth.server_key'),
                'Accept' => 'application/json',
            ])
            ->timeout(10)
            ->post('/api/provision', $payload);
    }
}
