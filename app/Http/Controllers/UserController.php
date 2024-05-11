<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserResource;
use App\Models\PasswordReset;
use App\Models\Profile;
use App\Models\User;
use App\Models\Wpda;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;


// Halo
class UserController extends Controller
{
    public function show($userId)
    {
        $user = User::with(['userProfile', 'partner'])->find($userId);

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


    public function updateFullName(Request $request, $userId)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
        ]);

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->full_name = $request->input('full_name');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Nama lengkap berhasil diperbarui',
            'user' => $user,
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

    public function sendVerifyEmail($email)
    {
        if (auth()->user()) {
            $user =   User::where('email', $email)->get();
            if (count($user) > 0) {


                $random =  Str::random(40);
                $domain = URL::to('/');
                $url = $domain . '/verify-mail/' . $random;

                $data['url'] = $url;
                $data['email'] = $email;
                $data['title'] = "Email Verification";
                $data['body'] = "Please click here to below to verify your mail.";
                Mail::send('verifyMail', ['data' => $data], function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['title']);
                });


                $user = User::find($user[0]['id']);
                $user->remember_token = $random;
                $user->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Mail sent successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not found!',
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User is not Authenticated',
            ]);
        }
    }

    public function verificationMail($token)
    {
        $user = User::where('remember_token', $token)->get();
        if (count($user) > 0) {
            $dateTime = Carbon::now()->format('Y-m-d H:i:s');
            $user = User::find($user[0]['id']);
            $user->remember_token = '';
            $user->verified = 1;
            $user->email_verified_at = $dateTime;
            $user->save();

            return view('success_verified_email');
        } else {
            return view('404');
        }
    }

    public function forgetPassword(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->get();
            if (count($user) > 0) {
                $token = Str::random(40);
                $domain = URL::to('/');
                $url = $domain . '/reset-password?token=' . $token;

                $data['url'] = $url;
                $data['email'] = $request->email;
                $data['title'] = "Password Reset";
                $data['body'] = "Please click on below link to reset you password.";

                Mail::send('forgetPasswordMail', ['data' => $data], function ($message) use ($data) {
                    $message->to($data['email'])->subject($data['title']);
                });
                $dateTime =  Carbon::now()->format('Y-m-d H:i:s');
                PasswordReset::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => $dateTime,
                    ],
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Please check your mail to reset your password.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found!',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function resetPasswordLoad(Request $request)
    {
        // Ambil data reset password berdasarkan token dari database
        $resetData = PasswordReset::where('token', $request->token)->first();

        // Periksa apakah token ada dan data reset password ditemukan
        if ($resetData) {
            // Ambil data pengguna berdasarkan email yang ditemukan di data reset password
            $user = User::where('email', $resetData->email)->first();

            // Periksa apakah pengguna ditemukan
            if ($user) {
                // Jika pengguna ditemukan, tampilkan halaman reset password
                return view('resetPassword', compact('user'));
            }
        }

        // Jika token tidak ditemukan atau data pengguna tidak ditemukan, arahkan ke halaman 404
        return view('404');
    }

    // Password Reset Functionality
    // Password Reset Functionality
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Ambil user berdasarkan ID yang diberikan
        $user = User::find($request->id);

        // Periksa apakah user ditemukan
        if ($user) {
            // Hash atau enkripsi password baru sebelum disimpan
            $user->password = Hash::make($request->password);
            $user->save();

            // Hapus semua token reset password yang terkait dengan email pengguna
            PasswordReset::where('email', $user->email)->delete();

            // Tampilkan pesan berhasil
            return "<h1>Your password has been reset successfully.</h1>";
        } else {
            // Tampilkan halaman 404 jika user tidak ditemukan
            abort(404);
        }
    }
}
