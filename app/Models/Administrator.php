<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model
{
    protected $table = 'administrators';
    protected $fillable = ['user_id', 'name', 'profile_picture'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
