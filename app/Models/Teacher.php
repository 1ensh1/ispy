<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = ['user_id', 'name', 'status', 'profile_picture'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classLists()
    {
        return $this->hasMany(ClassList::class, 'teacher_id');
    }
}
