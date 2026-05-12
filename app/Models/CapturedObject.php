<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapturedObject extends Model
{
    protected $table = 'captured_objects';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'vocabulary_id', 'captured_image_url',
        'is_successful_match', 'captured_at',
    ];

    protected $casts = [
        'is_successful_match' => 'boolean',
        'captured_at'         => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
