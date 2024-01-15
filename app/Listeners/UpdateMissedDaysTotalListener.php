<?php

namespace App\Listeners;

use App\Events\UpdateMissedDaysTotalEvent;
use App\Models\Profile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class UpdateMissedDaysTotalListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(UpdateMissedDaysTotalEvent $event)
    {
        $userId = $event->userId;

        // Ambil data dari API GetWpdabyUserId
        $apiUrl = "http://127.0.0.1/diamond-generation-service/public/api/wpda/history/{$userId}"; // Ganti dengan URL API yang sesuai
        $apiResponse = Http::get($apiUrl);

        if ($apiResponse->successful()) {
            $data = $apiResponse->json();
            $missedDaysTotal = $data['missed_days_total'];

            // Perbarui kolom missed_days_total pada tabel profiles
            Profile::where('user_id', $userId)->update([
                'missed_days_total' => $missedDaysTotal,
            ]);
        }
    }
}
