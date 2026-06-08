<?php

namespace App\Http\Controllers;

use App\Models\CmsAnnouncement;
use App\Models\CmsContent;

class LandingController extends Controller
{
    public function index()
    {
        $cms = CmsContent::all()->keyBy('section_key');

        $announcements = CmsAnnouncement::where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit(6)
            ->get();

        return view('landing', compact('cms', 'announcements'));
    }
}
