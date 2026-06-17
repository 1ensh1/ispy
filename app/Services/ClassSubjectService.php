<?php

namespace App\Services;

use App\Models\ClassSubject;
use App\Models\Student;
use App\Models\Teacher;

/**
 * Single source of truth for teacher ⇄ class+subject assignment.
 *
 * class_subjects has a UNIQUE (class_list_id, subject) constraint — at most ONE
 * row per (class, subject) can ever exist (active or archived). That means a
 * subject is held by exactly zero or one teacher at a time; two teachers can
 * never hold the same subject on the same class. Both the Teacher View
 * ("Assign Existing Class") and the User Management ("Edit Teacher") paths call
 * these methods so the rule is enforced identically everywhere.
 */
class ClassSubjectService
{
    /**
     * Assign a teacher to one or more subjects on a class.
     *
     * Per subject:
     *  - active row already this teacher's      → skipped (no-op)
     *  - active row held by a DIFFERENT teacher → blocked (never stolen, never duplicated)
     *  - no active row (free, or only archived) → (re)assigned to this teacher
     *
     * @param  array<int,string>  $subjects
     * @return array{created: string[], skipped: string[], blocked: array<string,string>}
     *         `blocked` maps subject => the blocking teacher's name (for messaging).
     */
    public function assign(int $classListId, int $teacherId, array $subjects): array
    {
        $created = [];
        $skipped = [];
        $blocked = [];

        foreach (array_unique($subjects) as $subject) {
            if (! in_array($subject, ['English', 'Filipino'], true)) {
                continue;
            }

            // Unique (class_list_id, subject) ⇒ at most one row exists, ever.
            $row = ClassSubject::where('class_list_id', $classListId)
                ->where('subject', $subject)
                ->first();

            if ($row && $row->archived_at === null) {
                if ((int) $row->teacher_id === $teacherId) {
                    $skipped[] = $subject;                       // already theirs
                } else {
                    $name = optional(Teacher::find($row->teacher_id))->name ?? 'another teacher';
                    $blocked[$subject] = $name;                  // held by someone else
                }
                continue;
            }

            if ($row) {
                // An archived row occupies the unique slot — reuse it rather than
                // insert (a fresh insert would violate the unique constraint).
                $row->teacher_id  = $teacherId;
                $row->archived_at = null;
                $row->created_at  = now();
                $row->save();
            } else {
                ClassSubject::create([
                    'class_list_id' => $classListId,
                    'teacher_id'    => $teacherId,
                    'subject'       => $subject,
                    'created_at'    => now(),
                ]);
            }

            $created[] = $subject;
        }

        return compact('created', 'skipped', 'blocked');
    }

    /**
     * Hard-delete a teacher's active assignment for one subject on a class.
     * (Archiving is reserved for whole-class archival — not individual unassign.)
     *
     * Guard: if the class has active students, the subject would be left with no
     * teacher while students remain, so the delete is BLOCKED. Under the unique
     * constraint there is never "another teacher still covering it" — if the row
     * exists it is the only one, hence the single block-or-allow branch.
     *
     * @return bool  true if deleted, false if blocked by active students.
     */
    public function unassign(int $classListId, int $teacherId, string $subject): bool
    {
        if ($this->classHasActiveStudents($classListId)) {
            return false;
        }

        ClassSubject::where('class_list_id', $classListId)
            ->where('teacher_id', $teacherId)
            ->where('subject', $subject)
            ->whereNull('archived_at')
            ->delete();

        return true;
    }

    /**
     * Whether the class has at least one active (non-archived) student.
     */
    public function classHasActiveStudents(int $classListId): bool
    {
        return Student::where('class_list_id', $classListId)
            ->whereNull('archived_at')
            ->exists();
    }
}
