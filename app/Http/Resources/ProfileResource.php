<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
        // return [
        //     'id' => $this->id,
        //     'user_id' => $this->user_id,
        //     'address' => $this->address,
        //     'phone_number' => $this->phone_number,
        //     'gender' => $this->gender,
        //     'age' => $this->age,
        //     'birth_place' => $this->birth_place,
        //     'birth_date' => $this->birth_date,
        //     'grade' => $this->grade,
        //     'created_at' => $this->created_at,
        //     'missed_days_total' => $this->missed_days_total,
        //     'user' => new UserResource::collection(),
        // ];
    }
}
