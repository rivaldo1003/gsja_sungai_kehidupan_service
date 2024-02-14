<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Comment;
use App\Models\Profile;
use App\Models\User;
use App\Models\Wpda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function googleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Verifikasi token Google menggunakan library atau SDK yang sesuai
        $googleUser = $this->verifyGoogleToken($request->token);

        if (!$googleUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google token.',
            ], 401);
        }

        // Cek apakah pengguna dengan email Google sudah terdaftar di sistem Anda
        $user = User::where('email', $googleUser['email'])->first();

        if (!$user) {
            // Jika belum terdaftar, daftarkan pengguna baru atau sesuaikan sesuai kebutuhan Anda
            $user = new User();
            $user->full_name = $googleUser['name'];
            $user->email = $googleUser['email'];
            $user->save();
        }

        // Generate token untuk pengguna
        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login with Google successful',
            'user' => new UserResource($user->loadMissing('userProfile')),
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak valid.'
            ]);
        }

        if ($user->approval_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna masih menunggu persetujuan. Harap menunggu persetujuan.'
            ], 401); // Unauthorized
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Berhasil masuk',
            'user' => new UserResource($user->loadMissing('userProfile')),
            'token' => $token,
        ]);
    }

    function saveSubscriptionId(Request $request, $id)
    {
        $request->validate([
            'device_token' => 'required',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Memeriksa apakah pengguna terotentikasi
        if (Auth::check()) {
            // Memeriksa apakah pengguna terotentikasi adalah pengguna yang ingin disimpan device token-nya
            if ($user->id !== Auth::user()->id) {
                return response()->json([
                    'message' => 'You are not authorized to send subscription id to this user'
                ], 403);
            }
        } else {
            // Jika pengguna tidak terotentikasi, kembalikan respons 401 Unauthorized
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user->device_token = $request->device_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Subscription id berhasil dikirim',
            'data' => new UserResource($user),
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Email sudah terdaftar'
            ]);
        }

        $user = new User();
        $user->full_name = $request->input('full_name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));

        // Save the user to the database
        $user->save();

        // Generate account number after saving the user
        $accountId = str_pad($user->id, 4, '0', STR_PAD_LEFT);
        $user->account_number = 'DG' . $accountId;

        // Update user with account number
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil mendaftar',
            'data' => new UserResource($user),
        ]);
    }

    public function getUsers()
    {
        $users = User::with('wpdaHistory')->get(); // Mengambil semua pengguna dengan history Wpda

        $usersWithTotalWpda = $users->map(function ($user) {
            $user['total_wpda'] = $user->wpdaHistory->count();
            $user['missed_days_total'] = $user->profile->missed_days_total ?? 0; // Menambahkan missed_days_total ke respons
            return $user;
        });

        $totalUser = User::count();

        return response()->json([
            'success' => true,
            "message" =>  "User data with wpda history retrieved successfully",
            'total_user' => $totalUser,
            'users_data'  => UserResource::collection($usersWithTotalWpda->loadMissing('userProfile')),
        ]);
    }


    public function logout(Request $request)
    {
        // Hapus access token saat ini
        $request->user()->currentAccessToken()->delete();

        // Hapus device_token
        $user = $request->user();
        $user->device_token = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Anda berhasil keluar'
        ]);
    }




    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',

            ], 404);
        }
        // Hapus semua komentar yang terkait dengan wpda yang dimiliki oleh pengguna
        Comment::whereIn('wpda_id', function ($query) use ($userId) {
            $query->select('id')
                ->from('wpdas')
                ->where('user_id', $userId);
        })->delete();

        // Hapus data wpda milik pengguna
        Wpda::where('user_id', $userId)->delete();

        // Hapus data profile pengguna
        Profile::where('user_id', $userId)->delete();



        // Hapus pengguna itu sendiri
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna dan semua data yang terkait dengan pengguna berhasil dihapus',
        ]);
    }


    public function approve($id)
    {


        $user = User::findOrFail($id);

        $user->approval_status = 'approved';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Status persetujuan berhasil diperbarui',
            'data' => new UserResource($user),
        ]);
    }

    public function getTotalUsers()
    {
        $totalUsers = User::count();

        return response()->json([
            'success' => true,
            'total_user' => $totalUsers,
        ]);
    }

    public function getTotalUsersWithWpda()
    {
        $totalUsersWithWpda = Wpda::distinct('user_id')->count('user_id');

        return response()->json([
            'success' => true,
            'total_users_with_wpda' => $totalUsersWithWpda,
        ]);
    }

    public function test()
    {
    }
}
