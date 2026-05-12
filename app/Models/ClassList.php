<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassList extends Model
{
    protected $table = 'class_lists';

    protected $fillable = ['teacher_id', 'class_name', 'unified_classroom_pin'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_list_id');
    }
}
