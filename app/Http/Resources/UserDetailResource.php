<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
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
        ];
    }
}
