<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularySuggestion extends Model
{
    protected $table = 'vocabulary_suggestions';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id', 'english_label', 'filipino_label',
        'category', 'status', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
