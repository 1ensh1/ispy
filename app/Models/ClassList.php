<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassList extends Model
{
    protected $table = 'class_lists';

    protected $fillable = ['teacher_id', 'class_name', 'subject', 'unified_classroom_pin'];

    protected $casts = ['archived_at' => 'datetime'];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_list_id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }
}
