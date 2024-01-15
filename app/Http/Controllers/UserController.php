<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Models\User;
use App\Models\Wpda;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($userId)
    {
        $user = User::with('userProfile')->find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new UserDetailResource($user),
        ]);
    }

    public function getTotalUsers()
    {
        $totalUsers = User::count();
        $totalUsersWithWpda = Wpda::distinct('user_id')->count('user_id');

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'total_user_with_wpda' => $totalUsersWithWpda,
            ]
        ]);
    }

    public function verifyUser(Request $request)
    {
        // Lakukan validasi atau verifikasi email di sini
        // ...

        // Jika verifikasi berhasil, update kolom 'verified' menjadi 1
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['verified' => 1]);
            return response()->json(['message' => 'User berhasil diverifikasi'], 200);
        } else {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
    }

    public function getNewUsersLastMonth()
    {
        $startDate = now()->subMonth();
        $totalUsers = User::count();
        $totalUsersWithWpda = Wpda::distinct('user_id')->count('user_id');
        $newUsers = User::where('created_at', '>=', $startDate)->geT();
        $totalNewUsers = $newUsers->count();

        return response()->json([
            'success' => true,
            'total_users' => $totalUsers,
            'total_new_users' => $totalNewUsers,
            'total_user_with_wpda' => $totalUsersWithWpda,
            'new_users' => UserResource::collection($newUsers),

        ]);
    }

    public function getMonthlyDataForAllUsers()
    {
        $users = User::all();
        $monthlyData = [];

        foreach ($users as $user) {
            $userId = $user->id;

            $wpdaData = Wpda::where('user_id', $userId)->get();
            $registrationDate = Carbon::parse($user->created_at);
            $today = Carbon::now();

            $totalDays = max(0, $registrationDate->diffInDays($today));
            $daysWithWpda = $wpdaData->count();

            // Menghitung missed days total hanya sampai hari ini
            $missedDaysTotal = max(0, $totalDays - $daysWithWpda);

            $monthlyUserData = [];

            // Ambil data untuk bulan saat ini saja
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            // Dapatkan semua hari dalam rentang bulan yang ditentukan
            $allDays = collect(CarbonPeriod::create($startDate, $endDate)->toArray());

            // Dapatkan hari-hari ketika WPDA diunggah
            $uploadedDays = $wpdaData->pluck('created_at')->map(function ($date) {
                return Carbon::parse($date)->startOfDay();
            });

            // Hitung jumlah hari yang tidak diunggah (missed days)
            $missedDaysThisMonth = $allDays->filter(function ($day) use ($today) {
                return $day->lte($today);
            })->diff($uploadedDays)->count();

            $totalWpdaMonthly = $wpdaData->whereBetween('created_at', [$startDate, $endDate])->count();

            $monthlyUserData[] = [
                'month' => $startDate->format('F Y'),
                'total_wpda' => $totalWpdaMonthly,
                'missed_days_total' => $missedDaysThisMonth,
            ];

            $profile = Profile::where('user_id', $userId)->first();
            $grade = $profile ? $profile->grade : '';
            $full_name = $user->full_name;
            $email = $user->email;
            $profile_picture = $profile ? $profile->profile_picture : '';

            $monthlyData[] = [
                'user_id' => $userId,
                'full_name' => $full_name,
                'email' => $email,
                'profile_picture' => $profile_picture,
                'grade' => $grade,
                'monthly_data' => $monthlyUserData,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $monthlyData,
        ]);
    }
}
