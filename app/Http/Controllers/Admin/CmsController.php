<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsAnnouncement;
use App\Models\CmsContent;
use App\Services\SupabaseStorageService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CmsController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $sections = CmsContent::all()->keyBy('section_key');

        $perPage = (int) $request->query('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $announcements = CmsAnnouncement::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());

        return view('admin.cms.index', compact('sections', 'announcements', 'perPage'));
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
            'file_url'     => 'nullable|string|max:1000',
            'is_published' => 'nullable|boolean',
            'remove_photo' => 'nullable|boolean',
        ]);

        $supabase = new SupabaseStorageService;

        if ($request->has('title')) {
            $section->title = $request->input('title');
        }

        if ($request->has('body')) {
            $section->body = $request->input('body');
        }

        if ($request->hasFile('image') && ! str_starts_with($sectionKey, 'tamatech_member_')) {
            $extension = $request->file('image')->getClientOriginalExtension() ?: 'jpg';
            $filename  = $sectionKey . '_' . time() . '.' . $extension;
            $binary    = file_get_contents($request->file('image')->getRealPath());
            $imageUrl  = $supabase->uploadImage($binary, $filename, 'cms-images', $request->file('image')->getMimeType());
            if ($imageUrl) {
                $section->image_url = $imageUrl;
            }
        }

        // TamaTech member profile photos: stored under a tamatech/ prefix and
        // supports removing the current photo via a remove_photo flag.
        $tamatechPhotoChanged = false;
        if (str_starts_with($sectionKey, 'tamatech_member_')) {
            if ($request->hasFile('image')) {
                $extension = $request->file('image')->getClientOriginalExtension() ?: 'jpg';
                $filename  = 'tamatech/' . $sectionKey . '_' . time() . '.' . $extension;
                $binary    = file_get_contents($request->file('image')->getRealPath());
                $imageUrl  = $supabase->uploadImage($binary, $filename, 'cms-images', $request->file('image')->getMimeType());
                if ($imageUrl) {
                    $section->image_url   = $imageUrl;
                    $tamatechPhotoChanged = true;
                }
            } elseif ($request->boolean('remove_photo')) {
                $section->image_url   = null;
                $tamatechPhotoChanged = true;
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

        // Plain-text video URL (teaser videos store a YouTube embed / direct link in file_url).
        if ($request->has('file_url')) {
            $url = $request->input('file_url') ?: null;

            // Teaser video URLs are pasted as share/watch links; convert them to an
            // embeddable format before storing so they render in an iframe.
            if ($url !== null && str_starts_with($sectionKey, 'teaser_video_')) {
                $url = $this->convertToEmbedUrl($url);
            }

            $section->file_url = $url;
        }

        if ($request->has('is_published')) {
            $section->is_published = $request->boolean('is_published');
        }

        $section->updated_at = now();
        $section->save();

        if (str_starts_with($sectionKey, 'tamatech_member_')) {
            if ($tamatechPhotoChanged) {
                self::log('CMS Edit', 'Updated TamaTech member photo: ' . ($section->title ?: $sectionKey));
            } else {
                self::log('CMS Edit', 'updated TamaTech member: ' . ($section->title ?: $sectionKey));
            }
        } elseif ($sectionKey === 'tamatech_intro') {
            self::log('CMS Edit', 'updated TamaTech intro section');
        } elseif (str_starts_with($sectionKey, 'teaser_video_')) {
            self::log('CMS Edit', 'updated CMS section: ' . $sectionKey);
        } else {
            self::log('CMS Edit', "updated CMS section '" . ($section->title ?: $sectionKey) . "'");
        }

        return response()->json([
            'success' => true,
            'data'    => $section,
        ]);
    }

    /**
     * Convert a pasted video share/watch URL into an embeddable iframe URL.
     * Handles YouTube short, watch, and embed links plus Google Drive share
     * and preview links. Any other URL is returned unchanged.
     */
    private function convertToEmbedUrl(string $url): string
    {
        // YouTube short URL: https://youtu.be/{id}?...
        if (str_contains($url, 'youtu.be/')) {
            $id = explode('?', explode('youtu.be/', $url, 2)[1])[0];

            return 'https://www.youtube.com/embed/' . $id;
        }

        // YouTube embed URL: already embeddable.
        if (str_contains($url, 'youtube.com/embed')) {
            return $url;
        }

        // YouTube watch URL: https://www.youtube.com/watch?v={id}&...
        if (str_contains($url, 'youtube.com/watch')) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            if (! empty($query['v'])) {
                return 'https://www.youtube.com/embed/' . $query['v'];
            }

            return $url;
        }

        // Google Drive preview URL: already embeddable.
        if (str_contains($url, '/preview')) {
            return $url;
        }

        // Google Drive share URL: https://drive.google.com/file/d/{id}/view?...
        if (str_contains($url, 'drive.google.com/file/d/')) {
            $id = explode('/', explode('/file/d/', $url, 2)[1])[0];

            return 'https://drive.google.com/file/d/' . $id . '/preview';
        }

        return $url;
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

        self::log('CMS Edit', "created CMS announcement '{$announcement->title}'");

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

        self::log('CMS Edit', "updated CMS announcement '{$announcement->title}'");

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroyAnnouncement($id)
    {
        $announcement = CmsAnnouncement::findOrFail($id);
        $announcement->delete();

        self::log('CMS Edit', "deleted CMS announcement '{$announcement->title}'");

        return response()->json([
            'success' => true,
        ]);
    }
}
