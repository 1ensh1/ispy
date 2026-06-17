<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the TamaTech team CMS rows. Mirrors the seeding pattern used in the
     * create_cms_content_table migration (DB::table insert). The cms_content
     * table has no created_at column — only updated_at is touched here.
     */
    public function up(): void
    {
        $rows = [
            'tamatech_intro' => [
                'title' => 'Meet the Team',
                'body'  => 'TamaTech is the student development team behind iSpy World. We designed and built the bilingual learning experience — from the Android app to this web portal — to help young learners explore vocabulary in English and Filipino.',
            ],
            'tamatech_member_1' => [
                'title' => 'Rome Gabriel Capuno',
                'body'  => 'Lead Web Developer',
            ],
            'tamatech_member_2' => [
                'title' => 'Walter Andrew Española',
                'body'  => 'Lead Mobile Developer',
            ],
            'tamatech_member_3' => [
                'title' => 'John Romar Francisco',
                'body'  => 'Mobile Developer',
            ],
            'tamatech_member_4' => [
                'title' => 'Rye Angelo Yanela',
                'body'  => 'UI/UX Designer',
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
            ->whereIn('section_key', [
                'tamatech_intro',
                'tamatech_member_1',
                'tamatech_member_2',
                'tamatech_member_3',
                'tamatech_member_4',
            ])
            ->delete();
    }
};
