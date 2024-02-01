<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{

    use HasApiTokens, HasFactory, Notifiable;
    protected $table = "users";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        "full_name",
        "email",
        "profile_picture",
        "verified",
        "device_token"
    ];

    public function wpdaHistory()
    {
        return $this->hasMany(Wpda::class, "user_id", "id");
    }

    public function userProfile()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function likes()
    {
        return $this->belongsToMany(Wpda::class, 'likes');
    }
}
