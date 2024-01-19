<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'wpda_id' => $this->wpda_id,
            'user_id' => $this->user_id,
            'comments_content' => $this->comments_content, // Pastikan ini sesuai dengan nama kolom di model komentar Anda
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name, // Ganti dengan nama atribut yang sesuai di model User
                'email' => $this->user->email, // Ganti dengan nama atribut yang sesuai di model User
                  'profile_picture' => str_replace('public/', '', $this->user->userProfile->profile_picture), // Menggunakan URL yang sudah ada tanpa 'public'
            ],
        ];
    }
}
