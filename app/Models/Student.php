<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['parent_id', 'class_list_id', 'name', 'profile_icon', 'parent_password'];

    public function parentUser()
    {
        return $this->belongsTo(ParentUser::class, 'parent_id');
    }

    public function classList()
    {
        return $this->belongsTo(ClassList::class, 'class_list_id');
    }

    public function parentProfile()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function masteryScores()
    {
        return $this->hasMany(MasteryScore::class);
    }

    public function studentProgress()
    {
        return $this->hasMany(StudentProgress::class);
    }

    public function capturedObjects()
    {
        return $this->hasMany(CapturedObject::class);
    }
}
