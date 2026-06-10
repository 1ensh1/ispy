<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    protected $table = 'parents';

    protected $fillable = ['user_id', 'name', 'contact_number', 'profile_picture'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function engagementRecords()
    {
        return $this->hasMany(EngagementRecord::class, 'parent_id');
    }

    public function faceToFaceBookings()
    {
        return $this->hasMany(FaceToFaceBooking::class, 'parent_id');
    }
}
