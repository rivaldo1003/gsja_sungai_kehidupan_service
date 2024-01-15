<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = "profiles";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        "address",
        "phone_number",
        "gender",
        "age",
        "grade",
        "birth_place",
        "birth_date",
        "profile_completed",
        "profile_picture",
        'missed_days_total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
