<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['parent_id', 'class_list_id', 'name', 'profile_icon', 'profile_picture', 'parent_password', 'archived_at'];

    protected $casts = ['archived_at' => 'datetime'];

    protected $hidden = ['parent_password'];

    public function scopeActive($query)   { return $query->whereNull('archived_at'); }
    public function scopeArchived($query) { return $query->whereNotNull('archived_at'); }

    public function parentUser()
    {
        return $this->belongsTo(ParentUser::class, 'parent_id');
    }

    /**
     * The owning parent (parents table). Alias kept alongside the existing
     * parentUser()/parentProfile() relationships so code/eager-loads that
     * reference the canonical "parent" name resolve correctly.
     */
    public function parent()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function classList()
    {
        return $this->belongsTo(ClassList::class, 'class_list_id');
    }

    public function parentProfile()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function masteryScores()
    {
        return $this->hasMany(MasteryScore::class);
    }

    public function studentProgress()
    {
        return $this->hasMany(StudentProgress::class);
    }

    public function capturedObjects()
    {
        return $this->hasMany(CapturedObject::class);
    }
}
