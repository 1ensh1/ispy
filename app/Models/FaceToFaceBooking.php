<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceToFaceBooking extends Model
{
    protected $table = 'face_to_face_bookings';

    public const STATUSES = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'Rejected', 'No-show'];

    protected $fillable = [
        'slot_id', 'teacher_id', 'parent_id', 'purpose_of_meeting', 'status',
    ];

    public function slot()
    {
        return $this->belongsTo(ConsultationSlot::class, 'slot_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function parentProfile()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }
}
