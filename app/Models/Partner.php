<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user1_id',
        'user2_id',
        'partner_name',
        'children_count',
    ];

    // Relasi dengan model User untuk user1
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    // Relasi dengan model User untuk user2
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }
}
