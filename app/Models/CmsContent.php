<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    protected $table = 'cms_content';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'section_key',
        'title',
        'body',
        'image_url',
        'file_url',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'updated_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (CmsContent $content) {
            $content->updated_at = now();
        });
    }
}
