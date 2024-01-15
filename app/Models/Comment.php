<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['user_id', 'wpda_id', 'comments_content'];

    public function comentator()
    {
        return $this->belongsTo(User::class);
    }

    public function wpda()
    {
        return $this->belongsTo(Wpda::class);
    }
}
