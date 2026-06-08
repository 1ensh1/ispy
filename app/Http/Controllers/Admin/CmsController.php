<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CmsAnnouncement;
use App\Models\CmsContent;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CmsController extends Controller
{
    public function index()
    {
        $sections = CmsContent::all()->keyBy('section_key');

        $announcements = CmsAnnouncement::orderBy('created_at', 'desc')->get();

        return view('admin.cms.index', compact('sections', 'announcements'));
    }

    public function updateSection(Request $request, $sectionKey)
    {
        $section = CmsContent::where('section_key', $sectionKey)->first();

        if (! $section) {
            throw ValidationException::withMessages([
                'section_key' => "Unknown CMS section: {$sectionKey}",
            ]);
        }

        $request->validate([
            'title'        => 'nullable|string|max:255',
            'body'         => 'nullable|string',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'file'         => 'nullable|file|max:102400',
            'is_published' => 'nullable|boolean',
        ]);

        $supabase = new SupabaseStorageService;

        if ($request->has('title')) {
            $section->title = $request->input('title');
        }

        if ($request->has('body')) {
            $section->body = $request->input('body');
        }

        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension() ?: 'jpg';
            $filename  = $sectionKey . '_' . time() . '.' . $extension;
            $binary    = file_get_contents($request->file('image')->getRealPath());
            $imageUrl  = $supabase->uploadImage($binary, $filename, 'cms-images', $request->file('image')->getMimeType());
            if ($imageUrl) {
                $section->image_url = $imageUrl;
            }
        }

        if ($sectionKey === 'apk_download' && $request->hasFile('file')) {
            $extension = $request->file('file')->getClientOriginalExtension() ?: 'apk';
            $filename  = $sectionKey . '_' . time() . '.' . $extension;
            $binary    = file_get_contents($request->file('file')->getRealPath());
            $fileUrl   = $supabase->uploadImage($binary, $filename, 'cms-files', 'application/vnd.android.package-archive');
            if ($fileUrl) {
                $section->file_url = $fileUrl;
            }
        }

        if ($request->has('is_published')) {
            $section->is_published = $request->boolean('is_published');
        }

        $section->updated_at = now();
        $section->save();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'role'        => 'Admin',
            'action'      => 'CMS Edit',
            'description' => "Admin updated CMS section: {$sectionKey}",
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $section,
        ]);
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'is_published' => 'nullable|boolean',
        ]);

        $supabase = new SupabaseStorageService;

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension() ?: 'jpg';
            $filename  = 'announcement_' . time() . '.' . $extension;
            $binary    = file_get_contents($request->file('image')->getRealPath());
            $imageUrl  = $supabase->uploadImage($binary, $filename, 'cms-images', $request->file('image')->getMimeType());
        }

        $isPublished = $request->boolean('is_published');

        $announcement = CmsAnnouncement::create([
            'title'        => $request->input('title'),
            'body'         => $request->input('body'),
            'image_url'    => $imageUrl,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
        ]);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'role'        => 'Admin',
            'action'      => 'CMS Edit',
            'description' => "Admin created announcement: {$announcement->title}",
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $announcement,
        ]);
    }

    public function updateAnnouncement(Request $request, $id)
    {
        $announcement = CmsAnnouncement::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'image'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'is_published' => 'nullable|boolean',
        ]);

        $supabase = new SupabaseStorageService;

        $announcement->title = $request->input('title');
        $announcement->body  = $request->input('body');

        if ($request->hasFile('image')) {
            $extension = $request->file('image')->getClientOriginalExtension() ?: 'jpg';
            $filename  = 'announcement_' . time() . '.' . $extension;
            $binary    = file_get_contents($request->file('image')->getRealPath());
            $imageUrl  = $supabase->uploadImage($binary, $filename, 'cms-images', $request->file('image')->getMimeType());
            if ($imageUrl) {
                $announcement->image_url = $imageUrl;
            }
        }

        $isPublished = $request->boolean('is_published');
        $announcement->is_published = $isPublished;

        if ($isPublished && is_null($announcement->published_at)) {
            $announcement->published_at = now();
        }

        $announcement->save();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'role'        => 'Admin',
            'action'      => 'CMS Edit',
            'description' => "Admin updated announcement ID {$id}",
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroyAnnouncement($id)
    {
        $announcement = CmsAnnouncement::findOrFail($id);
        $announcement->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'role'        => 'Admin',
            'action'      => 'CMS Edit',
            'description' => "Admin deleted announcement ID {$id}",
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }
}
