<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    public $timestamps = false;

    protected $fillable = [
        'engagement_id', 'sender_role', 'sender_id',
        'message_body', 'sent_at', 'is_read',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function engagementRecord()
    {
        return $this->belongsTo(EngagementRecord::class, 'engagement_id');
    }
}
