<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    protected $table = 'student_progress';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'vocabulary_id', 'mode', 'attempts',
        'score', 'errors', 'mastery_weight', 'attempted_at',
    ];

    protected $casts = [
        'errors'       => 'array',
        'attempted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function vocabulary()
    {
        return $this->belongsTo(VocabularyLibrary::class, 'vocabulary_id');
    }
}
