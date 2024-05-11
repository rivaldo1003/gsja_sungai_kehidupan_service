<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WpdaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $formattedDate = date("Y-m-d H:i:s", strtotime($this->created_at));

        // Get the profile_picture path and remove "public/" prefix
        $profilePicture = optional($this->writer->userProfile)->profile_picture;
        $profilePictureWithoutPublic = str_replace('public/', '', $profilePicture);

        // Transform comments using CommentResource
        $comments = CommentResource::collection($this->whenLoaded('comments'));

        return [
            'id' => $this->id,
            'reading_book' => $this->reading_book,
            'verse_content' => $this->verse_content,
            'message_of_god' => $this->message_of_god,
            'application_in_life' => $this->application_in_life,
            'doa_tabernakel' => $this->doa_tabernakel,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'user_id' => $this->user_id,
            'writer' => [
                'id' => $this->writer->id,
                'full_name' => $this->writer->full_name,
                'email' => $this->writer->email,
                'device_token' => $this->writer->device_token,
                'profile_picture' => $profilePictureWithoutPublic,
                'ministry' => $this->writer->ministry,
            ],
            'comments' => $comments,
        ];
    }
}
