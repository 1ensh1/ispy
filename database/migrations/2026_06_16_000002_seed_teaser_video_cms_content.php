<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the two teaser-video CMS rows. Mirrors the seeding pattern used in the
     * create_cms_content_table migration (DB::table insert). The video URL is
     * stored in file_url and starts null (no video yet). The cms_content table
     * has no created_at column — only updated_at is touched here.
     */
    public function up(): void
    {
        $rows = [
            'teaser_video_web' => [
                'title' => 'Web App Preview',
                'body'  => 'See how iSpy World works on the browser.',
            ],
            'teaser_video_mobile' => [
                'title' => 'Mobile App Preview',
                'body'  => 'See how iSpy World works on the mobile app.',
            ],
        ];

        foreach ($rows as $key => $data) {
            DB::table('cms_content')->updateOrInsert(
                ['section_key' => $key],
                [
                    'title'        => $data['title'],
                    'body'         => $data['body'],
                    'image_url'    => null,
                    'file_url'     => null,
                    'is_published' => true,
                    'updated_at'   => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('cms_content')
            ->whereIn('section_key', ['teaser_video_web', 'teaser_video_mobile'])
            ->delete();
    }
};
