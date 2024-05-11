<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'role' => $this->role,
            'account_number' => $this->account_number,
            'profile_completed' => $this->profile_completed,
            'approval_status' => $this->approval_status,
            'user_profile' => new ProfileResource($this->whenLoaded('userProfile')),
            // Menambahkan informasi partner jika tersedia
            'partner' => $this->partner ? [
                'partner_name' => $this->partner->partner_name,
                'children_count' => $this->partner->children_count,
            ] : null,
        ];
    }
}
