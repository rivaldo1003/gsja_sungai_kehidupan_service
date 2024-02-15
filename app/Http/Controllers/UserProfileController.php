<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{

    public function index()
    {
        $profile = Profile::with('user')->get();
        return response()->json([
            'success' => true,
            'message' => 'Retrieved data successfully',
            'data' => ProfileResource::collection($profile),
        ]);
    }
    public function tesProfile()
    {
        $profile = Profile::with('user')->get();
        return response()->json([
            'success' => true,
            'message' => 'Retrieved data successfully',
            'data' => ProfileResource::collection($profile),
        ]);
    }

    public function createProfile(Request $request, $id)
    {
        $request->validate([
            'address' => 'required',
            'phone_number' => 'required',
            'gender' => 'required',
            'age' => 'required',
            'birth_place' => 'required',
            'birth_date' => 'required',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->id !== Auth::user()->id) {
            return response()->json([
                'message' => 'You are not authorized to create profile for this user'
            ], 403);
        }

        if ($user->profile_completed == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, your profile is completed',
            ]);
        }

        // Create the user's profile
        $profile = new Profile();
        $profile->address = $request->address;
        $profile->phone_number = $request->phone_number;
        $profile->gender = $request->gender;
        $profile->age = $request->age;
        $profile->birth_place = $request->birth_place;
        $profile->birth_date = $request->birth_date;
        $profile->user_id = Auth::user()->id;
        $profile->save();

        // Set the user's profile_completed to 1 after the profile is created
        $user->profile_completed = 1;
        $user->save();

        // If partner information is provided, create partner profile
        if ($request->filled('partner_name') || $request->filled('children_count')) {
            $partner = new Partner();
            $partner->user1_id = Auth::user()->id;
            $partner->partner_name = $request->input('partner_name');
            $partner->children_count = $request->input('children_count', 0); // default value 0 if not provided
            $partner->save();
        }

        // Return response
        return response()->json([
            'success' => true,
            'profile_completed' => $user->profile_completed,
            'message' => 'Profile successfully created',
            'data' => new ProfileResource($profile),
        ]);
    }

    public function updateProfile(Request $request, $userId)
    {
        $profile = Profile::where('user_id', $userId)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found'
            ]);
        }

        if ($profile->user_id !== Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, you are not authorized to update this profile'
            ]);
        }

        $allowedFields = ['address', 'phone_number', 'gender', 'age', 'birth_place', 'birth_date'];
        $requestData = $request->only($allowedFields);

        try {
            $profile->update($requestData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil pengguna berhasil diperbarui',
            'data' => new ProfileResource($profile),
        ]);
    }



    public function deleteProfile($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        if ($profile->user_id !== Auth::user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this profile',
            ], 403);
        }

        $profile->delete();

        $user = $profile->user;
        if ($user) {
            $user->profile_completed = 0;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'User profile has been deleted successfully',
            'data' => new ProfileResource($profile),
        ]);
    }

    public function uploadProfilePicture(Request $request, $userId)
    {
        $request->validate([
            'profile_picture' => 'required|file|max:2048',
        ]);

        $user = User::findOrFail($userId);

        if ($request->hasFile('profile_picture')) {
            $profilePicture = $request->file('profile_picture');

            // Check the file size before processing
            $fileSize = $profilePicture->getSize(); // Get file size in bytes
            $maxFileSize = 2048; // Maximum file size in kilobytes (2MB)

            if ($fileSize > $maxFileSize * 1024) {
                return response()->json(['message' => 'File size exceeds the limit of 2048 KB'], 400);
            }

            $filename = 'user_' . $userId . '.' . $profilePicture->getClientOriginalExtension();
            $path = $request->file('profile_picture')->storeAs('public/profile_pictures', $filename);

            // Pastikan profile sudah ada atau buat baru jika belum
            $profile = $user->userProfile ?? new Profile();
            $profile->profile_picture = $path;
            $profile->save();

            // Ubah path gambar profil menjadi URL lengkap
            $profilePictureUrl = url('/storage/' . $path);

            return response()->json(['profile_picture_url' => $profilePictureUrl]);
        }

        return response()->json(['message' => 'Failed to upload profile picture'], 500);
    }




    public function getProfilePicture($userId)
    {
        $user = User::findOrFail($userId);

        // Ubah ini sesuai dengan hubungan yang tepat antara User dan Profile
        $profile = $user->userProfile;

        if ($profile && $profile->profile_picture) {
            // Menggunakan fungsi url untuk menghasilkan URL dengan path yang diinginkan
            $path = url('storage/profile_pictures/user_' . $userId . '.' . pathinfo($profile->profile_picture, PATHINFO_EXTENSION));
            $pathWithoutPublic = str_replace('public/', '', $path);
            return response()->json(['profile_picture' => $pathWithoutPublic]);
        }

        return response()->json(['message' => 'Profile picture not found'], 404);
    }





    public function deleteProfilePicture($userId)
    {
        $user = User::findOrFail($userId);

        // Ubah ini sesuai dengan hubungan yang tepat antara User dan Profile
        $profile = $user->userProfile;

        if ($profile && $profile->profile_picture) {
            $picturePath = $profile->profile_picture;

            // Hapus gambar profil dari disk 'public'
            if (Storage::disk('public')->exists($picturePath)) {
                Storage::disk('public')->delete($picturePath);
            }

            // Hapus symlink
            $symlinkPath = public_path('storage/profile_pictures');
            if (is_link($symlinkPath)) {
                unlink($symlinkPath);
            }

            // Hapus path gambar profil pada model Profile
            $profile->profile_picture = null;
            $profile->save();

            return response()->json(['success' => true, 'message' => 'Gambar profil berhasil dihapus']);
        }

        return response()->json(['success' => false, 'message' => 'Profile picture not found'], 404);
    }
    public function getUserGenderTotal()
    {
        $totalMaleUsers = Profile::where('gender', 'Laki-Laki')->count();
        $totalFemaleUsers = Profile::where('gender', 'Perempuan')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_male_users' => $totalMaleUsers,
                'total_female_users' => $totalFemaleUsers,
            ]
        ]);
    }
}
