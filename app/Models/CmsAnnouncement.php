<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAnnouncement extends Model
{
    protected $table = 'cms_announcements';

    protected $fillable = [
        'title',
        'body',
        'image_url',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
