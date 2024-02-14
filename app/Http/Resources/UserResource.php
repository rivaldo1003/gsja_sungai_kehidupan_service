<?php

namespace App\Http\Resources;

use App\Models\Wpda;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $date = new DateTime($this->created_at);
        $formattedDate = $date->format('d F Y H:i:s');
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'verified' => $this->verified,
            'created_at' => $formattedDate,
            'role' => $this->role,
            'profile_completed' => $this->profile_completed,
            'approval_status' => $this->approval_status,
            'total_wpda' => $this->total_wpda,
            'account_number' => $this->account_number,
            'device_token' => $this->device_token,
            'profile' => new ProfileResource($this->whenLoaded('userProfile')),
            'wpda_history' => new ProfileResource($this->whenLoaded('wpdaHistory')),

        ];
    }
}
