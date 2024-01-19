<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Resources\WpdaResource;
use App\Models\Like;
use App\Models\Profile;
use App\Models\User;
use App\Models\Wpda;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WpdaController extends Controller
{
    public function index()
    {
        $wpda = Wpda::with('writer:id,full_name,email')->get();

        return response()->json([
            'success' => true,
            'data' => WpdaResource::collection($wpda->loadMissing('comments')),
        ]);
    }

    public function createWpda(Request $request)
    {
        // Mendapatkan waktu saat ini sesuai timezone yang diinginkan
        $timezone = 'Asia/Jakarta'; // Ganti ini dengan timezone yang diinginkan
        $currentTime = Carbon::now($timezone)->format('Y-m-d');

        // Mengambil WPDA yang dibuat pengguna pada hari ini
        $user_id = Auth::user()->id;
        $wpdaCreatedToday = Wpda::where('user_id', $user_id)
            ->whereDate('created_at', $currentTime)
            ->count();

        // Memeriksa apakah pengguna sudah membuat WPDA hari ini
        if ($wpdaCreatedToday >= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah menguggah WPDA hari ini.',
            ], 400); // Mengembalikan response bad request dengan pesan kesalahan
        }

        // Validasi input
        $request->validate([
            'reading_book' => 'required',
            'verse_content' => 'required',
            'message_of_god' => 'required',
            'application_in_life' => 'required',
            'doa_tabernakel' => 'required',
        ]);

        // Simpan WPDA jika pengguna belum membuat WPDA hari ini
        $wpda = new Wpda();
        $wpda->user_id = $user_id;
        $wpda->reading_book = $request->input('reading_book');
        $wpda->verse_content = $request->input('verse_content');
        $wpda->message_of_god = $request->input('message_of_god');
        $wpda->application_in_life = $request->input('application_in_life');
        $wpda->doa_tabernakel = $request->input('doa_tabernakel');

        $createdAtFormat =  Carbon::now($timezone)->format('Y-m-d H:i:s');
        // Set waktu pembuatan dan perubahan WPDA
        $wpda->created_at = $createdAtFormat;
        $wpda->updated_at = $createdAtFormat;

        $wpda->save();

        $wpdaResource = new WpdaResource($wpda->loadMissing('writer:id,full_name,email'));

        return response()->json([
            'success' => true,
            'message' => 'WPDA berhasil dibuat',
            'data' => $wpdaResource,
        ]);
    }

   public function getByUserId($userId)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    $wpda = Wpda::where('user_id', $userId)
        ->with('writer:id,full_name,email')
        ->get();

    $totalWpda = $wpda->count();

    $registrationDate = Carbon::parse($user->created_at);
    $today = Carbon::now();

    $totalDays = max(0, $registrationDate->diffInDays($today));

    // Mengecek apakah user sudah melakukan wpda pada hari registrasi
    $missedDaysTotal = max(0, $totalDays - $totalWpda);

    Profile::updateOrCreate(
        ['user_id' => $userId],
        ['missed_days_total' => $missedDaysTotal]
    );

    // Menghitung total missed day dalam 7 hari terakhir
    $wpdaLast7Days = Wpda::where('user_id', $userId)
        ->where('created_at', '>', Carbon::now()->subDays(7)->startOfDay())
        ->get();

    $missedDaysLast7Days = max(0, 7 - $wpdaLast7Days->count());

    // Menghitung total missed day dalam 30 hari terakhir
    $wpdaLast30Days = Wpda::where('user_id', $userId)
        ->where('created_at', '>', Carbon::now()->subDays(30)->startOfDay())
        ->get();

    $missedDaysLast30Days = max(0, 30 - $wpdaLast30Days->count());

    // Logika untuk menghitung missed_days_total_this_month
    $missedDaysThisMonth = $this->calculateMissedDaysThisMonth($userId);

    $profile = Profile::where('user_id', $userId)->first();
    $grade = $profile ? $profile->grade : '';

    // Pindahkan baris update ke luar dari respons JSON
    Profile::where('user_id', $userId)->update([
        'missed_days_total' => $missedDaysTotal
    ]);

    return response()->json([
        'success' => true,
        'missed_days_total' => $missedDaysTotal + 1,
        'missed_days_last_7_days' => $missedDaysLast7Days,
        'missed_days_last_30_days' => $missedDaysLast30Days,
        'missed_days_total_this_month' => $missedDaysThisMonth,
        'grade' => $grade,
        'total_users' => User::count(),
        'total_wpda' => $totalWpda,
        'data' => WpdaResource::collection($wpda),
    ]);
}

private function calculateMissedDaysThisMonth($userId)
{
    $user = User::find($userId);
    $today = Carbon::now();

    // Hitung tanggal pendaftaran pengguna
    $registrationDate = Carbon::parse($user->created_at);

    // Hitung tanggal awal dengan mempertimbangkan tanggal pendaftaran
    $startDate = Carbon::createFromDate($today->year, $today->month, 1)->startOfMonth()->min($registrationDate);
    $endDate = $today->copy()->endOfMonth();

    // Ambil WPDA sesuai rentang waktu
    $wpdaThisMonth = Wpda::where('user_id', $userId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Dapatkan semua hari dalam rentang bulan yang ditentukan mulai dari tanggal pendaftaran
    $allDays = collect(CarbonPeriod::create($registrationDate, $endDate)->toArray());

    // Dapatkan hari-hari ketika WPDA diunggah
    $uploadedDays = $wpdaThisMonth->pluck('created_at')->map(function ($date) {
        return Carbon::parse($date)->startOfDay();
    });

    // Hitung jumlah hari yang tidak diunggah (missed days) mulai dari tanggal pendaftaran
    return $allDays
        ->filter(function ($day) use ($today) {
            return $day->lte($today);
        })
        ->reject(function ($day) use ($uploadedDays) {
            return $uploadedDays->contains($day);
        })
        ->count();
}





    public function updateWpda(Request $request, $id)
    {
        $wpda = Wpda::find($id);

        if (!$wpda) {
            return response()->json([
                'message' => 'WPDA not found'
            ]);
        }
        $request->validate([
            'reading_book' => 'required',
            'verse_content' => 'required',
            'message_of_god' => 'required',
            'application_in_life' => 'required',
            'doa_tabernakel' => 'required',
        ]);


        if ($wpda->user_id !== Auth::user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this WPDA!'

            ]);
        }

        $wpda->reading_book = $request->reading_book;
        $wpda->verse_content = $request->verse_content;
        $wpda->message_of_god = $request->message_of_god;
        $wpda->application_in_life = $request->application_in_life;
        $wpda->doa_tabernakel = $request->doa_tabernakel;
        $wpda->save();

        return response()->json([
            'success' => true,
            'message' => 'Data WPDA berhasil diperbarui',
            'data' => $wpda,
        ]);
    }

    public function delete($id)
{
    $wpda = Wpda::find($id);

    if (!$wpda) {
        return response()->json([
            'message' => 'Data WPDA tidak ditemukan'
        ], 404);
    }

    if ($wpda->user_id !== Auth::user()->id) {
        return response()->json([
            'message' => 'Anda tidak diizinkan menghapus WPDA ini!'
        ], 403);
    }

    // Periksa apakah WPDA diunggah dalam 24 jam terakhir
    $waktuUpload = Carbon::parse($wpda->created_at);
    $waktuSekarang = Carbon::now();

    if ($waktuSekarang->diffInHours($waktuUpload) > 12) {
        return response()->json([
            'message' => 'Anda hanya dapat menghapus WPDA dalam waktu 12 jam setelah diunggah'
        ], 403);
    }

    // Hapus semua komentar terkait dengan WPDA
    $wpda->comments()->delete();

    // Hapus WPDA
    $wpda->delete();

    return response()->json([
        'success' => true,
        'message' => 'WPDA berhasil dihapus',
    ]);
}



    // FILTER DATA WPDA
public function getWpdaByMonth($userId, Request $request)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    // Validasi input bulan dan tahun
    $request->validate([
        'month' => 'required|numeric|between:1,12',
        'year' => 'required|numeric',
    ]);

    $month = $request->input('month');
    $year = $request->input('year');

    $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

    $wpda = Wpda::where('user_id', $userId)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->with('writer:id,full_name,email')
        ->get();

    // Dapatkan semua hari dalam rentang bulan yang ditentukan
    $allDays = collect(CarbonPeriod::create($startDate, $endDate)->toArray());

    // Dapatkan hari-hari ketika WPDA diunggah
    $uploadedDays = $wpda->pluck('created_at')->map(function ($date) {
        return Carbon::parse($date)->startOfDay();
    });

    // Hitung jumlah hari yang tidak diunggah (missed days)
    $today = Carbon::today();
    $registrationDate = Carbon::parse($user->created_at);

    // Filter hari-hari dari tanggal pendaftaran
    $filteredDays = $allDays->filter(function ($day) use ($today, $registrationDate) {
        return $day->gte($registrationDate) && $day->lte($today);
    });

    // Hitung missed_days_this_month
    $missedDaysThisMonth = $filteredDays->diff($uploadedDays)->count();

    $totalWpda = $wpda->count();
    $totalAllWpda = Wpda::where('user_id', $userId)->count();

    // Mendapatkan keterangan bulan
    $monthName = Carbon::createFromDate(null, $month, null)->monthName;

    $wpdaData = Wpda::where('user_id', $userId)
        ->with('writer:id,full_name,email')
        ->get();

    $totalWpdaData = Wpda::where('user_id', $userId)->count();
    $totalDays = max(0, $registrationDate->diffInDays($today));
    $daysWithWpda = $wpdaData->count();

    $missedDaysTotal = max(0, $totalDays - $daysWithWpda);

    $totalUsers = User::count();

    // Mendapatkan grade
    $grade = $this->getGrade($missedDaysThisMonth);

    // Memperbarui kolom grade pada tabel profiles
    $profile = Profile::updateOrCreate(
        ['user_id' => $userId],
        ['grade' => $grade]
    );

    if (!$profile) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update profile grade',
        ], 500);
    }

    return response()->json([
        'success' => true,
        'grade' => $grade,
        'total_wpda' => $totalWpda,
        'total_users' => $totalUsers,
        'total_all_wpda' => $totalAllWpda,
        'missed_days_total_this_month' => $missedDaysThisMonth,
        'missed_days_total' => $missedDaysTotal,
        'month' => $monthName,
        'data' => WpdaResource::collection($wpda),
    ]);
}




    private function getGrade($missedDays)
    {
        if ($missedDays < 4) {
            return 'A';
        } elseif ($missedDays < 8) {
            return 'B';
        } else {
            return 'C';
        }
    }
}
