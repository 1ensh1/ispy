<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = ['parent_id', 'class_list_id', 'name', 'profile_icon', 'profile_picture', 'parent_password', 'archived_at'];

    protected $casts = ['archived_at' => 'datetime'];

    protected $hidden = ['parent_password'];

    public function scopeActive($query)   { return $query->whereNull('archived_at'); }
    public function scopeArchived($query) { return $query->whereNotNull('archived_at'); }

    /**
     * Whether the given parent_password is already in use by another *active*
     * (non-archived) student. Archived students are intentionally excluded so a
     * freed-up password can be reused. Pass $exceptId to ignore the student
     * currently being edited (e.g. when changing their own password).
     */
    public static function passwordTaken(string $password, ?int $exceptId = null): bool
    {
        return static::active()
            ->where('parent_password', $password)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    /**
     * Generate a random parent_password that is guaranteed not to collide with
     * any existing active student. Retries on the (rare) chance of a clash so
     * student creation never fails on a coincidental duplicate.
     */
    public static function generateUniquePassword(int $length = 8): string
    {
        do {
            $password = Str::random($length);
        } while (static::passwordTaken($password));

        return $password;
    }

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
