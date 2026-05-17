<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAnnotation extends Model
{
    public $timestamps = false;

    protected $fillable = ['teacher_id', 'student_id', 'note', 'tags', 'annotation_date'];

    protected $casts = [
        'tags'            => 'array',
        'annotation_date' => 'date',
        'created_at'      => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
