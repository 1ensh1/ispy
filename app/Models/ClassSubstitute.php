<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ClassSubstitute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'class_list_id',
        'substitute_teacher_id',
        'assigned_by',
        'start_date',
        'end_date',
        'created_at',
    ];

    public function classList()
    {
        return $this->belongsTo(ClassList::class);
    }

    public function substituteTeacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function assignedByAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('start_date', '<=', today())
            ->where(function (Builder $q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', today());
            });
    }
}
