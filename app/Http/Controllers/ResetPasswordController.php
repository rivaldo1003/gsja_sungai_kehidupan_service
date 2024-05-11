<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        // Lakukan validasi permintaan
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Reset password menggunakan fasilitas bawaan Laravel
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            // Meng-hash kata sandi baru sebelum menyimpannya ke model pengguna
            $user->password = Hash::make($password);
            $user->save();
        });

        // Jika reset password berhasil
        if ($status == Password::PASSWORD_RESET) {
            // Kirim email reset password
            $this->sendResetEmail($request->email);

            // Berikan respons berdasarkan status reset password
            return response()->json(['message' => trans($status)], 200);
        } else {
            return response()->json(['error' => trans($status)], 400);
        }
    }

    protected function sendResetEmail($email)
    {
        // Generate reset token
        $token = Str::random(64);

        // Save token to database
        DB::table('password_resets')->updateOrInsert([
            'email' => $email,
        ], [
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Send email
        Mail::to($email)->send(new ResetPasswordMail($token));
    }
}
