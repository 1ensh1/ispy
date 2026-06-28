<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EngagementRecord extends Model
{
    protected $table = 'engagement_records';

    protected $fillable = ['parent_id', 'teacher_id', 'last_report_sent'];

    /**
     * Ensure an engagement_records row exists for every (parent, teacher) pair
     * derivable from the parent's non-archived students' class_subjects.
     *
     * class_subjects is the SOLE source of truth for teacher↔class assignment;
     * class_lists.teacher_id is legacy and intentionally ignored. A teacher who
     * teaches the parent's child in multiple subjects yields ONE row (distinct
     * teacher_id). Idempotent — firstOrCreate never duplicates or mutates rows.
     */
    public static function provisionForParent(int $parentId): void
    {
        $teacherIds = DB::table('students')
            ->where('students.parent_id', $parentId)
            ->whereNull('students.archived_at')
            ->join('class_subjects', 'students.class_list_id', '=', 'class_subjects.class_list_id')
            ->whereNull('class_subjects.archived_at')
            ->distinct()
            ->pluck('class_subjects.teacher_id');

        foreach ($teacherIds as $teacherId) {
            static::firstOrCreate([
                'parent_id'  => $parentId,
                'teacher_id' => $teacherId,
            ]);
        }
    }

    /**
     * Teacher IDs that currently have a LIVE class link to this parent, derived
     * fresh from class_subjects (sole source of truth). Same query path as
     * provisionForParent(). Honors archived_at IS NULL on students AND
     * class_subjects. Used to filter the messaging sidebar so stale threads are
     * hidden (never deleted) when the underlying class relationship is gone.
     *
     * @return array<int>
     */
    public static function validTeacherIdsForParent(int $parentId): array
    {
        return DB::table('students')
            ->where('students.parent_id', $parentId)
            ->whereNull('students.archived_at')
            ->join('class_subjects', 'students.class_list_id', '=', 'class_subjects.class_list_id')
            ->whereNull('class_subjects.archived_at')
            ->distinct()
            ->pluck('class_subjects.teacher_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Parent IDs that currently have a LIVE class link to this teacher, derived
     * fresh from class_subjects. Mirror of validTeacherIdsForParent() for the
     * teacher side. Honors archived_at IS NULL on class_subjects AND students.
     *
     * @return array<int>
     */
    public static function validParentIdsForTeacher(int $teacherId): array
    {
        return DB::table('class_subjects')
            ->where('class_subjects.teacher_id', $teacherId)
            ->whereNull('class_subjects.archived_at')
            ->join('students', 'students.class_list_id', '=', 'class_subjects.class_list_id')
            ->whereNull('students.archived_at')
            ->distinct()
            ->pluck('students.parent_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Deterministic class_name for the CURRENT live link between this parent and
     * the given teacher (lowest class_list_id when several exist). Null if no
     * live link — but a gated thread always has one when rendered.
     */
    public static function currentClassNameForPair(int $parentId, int $teacherId): ?string
    {
        return DB::table('students')
            ->where('students.parent_id', $parentId)
            ->whereNull('students.archived_at')
            ->join('class_subjects', 'students.class_list_id', '=', 'class_subjects.class_list_id')
            ->whereNull('class_subjects.archived_at')
            ->where('class_subjects.teacher_id', $teacherId)
            ->join('class_lists', 'class_lists.id', '=', 'class_subjects.class_list_id')
            ->orderBy('class_lists.id')
            ->value('class_lists.class_name');
    }

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
