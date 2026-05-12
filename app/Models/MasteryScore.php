<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasteryScore extends Model
{
    protected $table = 'mastery_scores';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'vocabulary_id', 'total_score', 'proficiency_level',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
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
