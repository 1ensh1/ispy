<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapturedObject extends Model
{
    protected $table = 'captured_objects';
    public $timestamps = false;

    // archived_at is intentionally NOT fillable — the mobile app is the sole writer.
    protected $fillable = [
        'student_id', 'vocabulary_id', 'captured_image_url',
        'is_successful_match', 'captured_at',
    ];

    protected $casts = [
        'is_successful_match' => 'boolean',
        'captured_at'         => 'datetime',
        'archived_at'         => 'datetime',
    ];

    /**
     * Global scope: hide rows the mobile app has soft-deleted via archived_at.
     * Propagates automatically to eager-loaded relationships (e.g. Student::capturedObjects()).
     */
    protected static function booted(): void
    {
        static::addGlobalScope('notArchived', function (Builder $builder) {
            $builder->whereNull('captured_objects.archived_at');
        });
    }

    /**
     * Escape hatch: include archived captures (removes the global scope).
     * Not applied anywhere yet — reserved for future admin views.
     */
    public function scopeWithArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('notArchived');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function vocabulary(): BelongsTo
    {
        return $this->belongsTo(VocabularyLibrary::class, 'vocabulary_id');
    }
}
