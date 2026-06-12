<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EngagementRecord extends Model
{
    protected $table = 'engagement_records';

    protected $fillable = ['parent_id', 'teacher_id', 'last_report_sent'];

    public function parentProfile()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'engagement_id');
    }
}
