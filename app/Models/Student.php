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
}
