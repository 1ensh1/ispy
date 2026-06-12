<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    public $timestamps = false;

    protected $fillable = ['class_list_id', 'teacher_id', 'subject', 'created_at', 'archived_at'];

    protected $casts = [
        'created_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function classList()
    {
        return $this->belongsTo(ClassList::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
