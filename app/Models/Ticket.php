<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'created_by_user_id',
        'created_by_role',
        'assigned_to_user_id',
        'title',
        'description',
        'status',
        'priority',
        'resolution_notes',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedToAdmin()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('created_by_user_id', $userId);
    }
}
