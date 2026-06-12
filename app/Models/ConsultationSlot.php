<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationSlot extends Model
{
    protected $table = 'consultation_slots';
    public $timestamps = false;

    protected $fillable = [
        'teacher_id', 'scheduled_date', 'time_start', 'time_end', 'is_available',
    ];

    protected $casts = [
        'is_available'   => 'boolean',
        'scheduled_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
