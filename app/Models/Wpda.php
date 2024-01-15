<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wpda extends Model
{
    protected $table = "wpdas";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        "user_id",
        "reading_book",
        "verse_content",
        "message_of_god",
        "application_in_life",
        "likes",
    ];


    public function writer()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }



    public function likers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes', 'wpda_id', 'user_id');
    }

    public function incrementTotalLikes()
    {
        $this->total_likes += 1;
        $this->save();
    }

    public function decrementTotalLikes()
    {
        $this->total_likes -= 1;
        $this->save();
    }
}
