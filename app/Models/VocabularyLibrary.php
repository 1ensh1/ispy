<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularyLibrary extends Model
{
    protected $table = 'vocabulary_library';

    protected $fillable = [
        'noun_anchor',
        'category',
        'filipino_label',
        'english_label',
        'complexity_level',
        'audio_status',
        'is_active',
        'filipino_audio_url',
        'english_audio_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
