<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherWordSetPreference extends Model
{
    protected $fillable = ['teacher_id', 'category', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
