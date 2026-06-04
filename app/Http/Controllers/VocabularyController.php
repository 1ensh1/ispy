<?php

namespace App\Http\Controllers;

use App\Models\VocabularyLibrary;
use App\Services\PexelsService;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VocabularyController extends Controller
{
    public function index(Request $request)
    {
        $search         = $request->query('search');
        $audioFilter    = $request->query('audio_status');
        $activeFilter   = $request->query('is_active');
        $categoryFilter = $request->query('category');
        $highlightId    = $request->query('highlight');

        $query = VocabularyLibrary::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('filipino_label', 'ilike', "%{$search}%")
                  ->orWhere('english_label', 'ilike', "%{$search}%")
                  ->orWhere('noun_anchor',   'ilike', "%{$search}%");
            });
        }

        if ($audioFilter && in_array($audioFilter, ['Complete', 'Partial', 'Missing'])) {
            $query->where('audio_status', $audioFilter);
        }

        if ($categoryFilter && in_array($categoryFilter, ['CVC', 'Multi-Syllabic'])) {
            $query->where('category', $categoryFilter);
        }

        if ($activeFilter === '1') {
            $query->where('is_active', 1);
        } elseif ($activeFilter === '0') {
            $query->where('is_active', 0);
        }

        $highlightId = $highlightId ? (int) $highlightId : null;

        if ($highlightId) {
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END ASC, english_label ASC', [$highlightId]);
        } else {
            $query->orderBy('english_label');
        }

        $words = $query->paginate(15)->appends(array_filter([
            'search'       => $search,
            'audio_status' => $audioFilter,
            'category'     => $categoryFilter,
            'is_active'    => $activeFilter,
            'highlight'    => $highlightId ?: null,
        ], fn($v) => $v !== null && $v !== ''));

        return view('admin.vocabulary', compact('words', 'search', 'audioFilter', 'categoryFilter', 'activeFilter', 'highlightId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filipino_label' => 'required|string|max:255',
            'english_label'  => 'required|string|max:255|unique:vocabulary_library,english_label',
            'category'       => 'required|in:CVC,Multi-Syllabic',
            'image'          => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'english_label.unique' => 'This English label already exists in the vocabulary library.',
        ]);

        $validated['english_label']    = ucfirst(strtolower($validated['english_label']));
        $validated['filipino_label']   = ucfirst($validated['filipino_label']);
        $validated['noun_anchor']      = $validated['english_label'];
        $validated['complexity_level'] = $validated['category'] === 'CVC' ? 1 : 2;
        $validated['audio_status']     = 'Missing';
        $validated['is_active']        = true;

        $label    = strtolower($validated['english_label']);
        $imageUrl = null;
        $supabase = new SupabaseStorageService;

        if ($request->hasFile('image')) {
            $filename = $label . '_' . time() . '.jpg';
            $binary   = file_get_contents($request->file('image')->getRealPath());
            $imageUrl = $supabase->uploadImage($binary, $filename);
        } else {
            $pexelsUrl = (new PexelsService)->fetchImage($validated['english_label']);
            if ($pexelsUrl) {
                $binary = @file_get_contents($pexelsUrl);
                if ($binary) {
                    $filename = $label . '_' . time() . '.jpg';
                    $imageUrl = $supabase->uploadImage($binary, $filename);
                }
            }
        }

        $validated['image_url'] = $imageUrl;
        unset($validated['image']);

        $word = VocabularyLibrary::create($validated);

        return redirect()->route('admin.vocabulary', ['highlight' => $word->id])
                         ->with('success', 'Vocabulary word added successfully!');
    }

    public function update(Request $request, VocabularyLibrary $vocabulary)
    {
        $validated = $request->validate([
            'filipino_label' => 'required|string|max:255',
            'english_label'  => 'required|string|max:255|unique:vocabulary_library,english_label,' . $vocabulary->id,
            'category'       => 'required|in:CVC,Multi-Syllabic',
            'is_active'      => 'nullable|boolean',
            'filipino_audio' => 'nullable|file|mimes:mpga,mp3|max:10240',
            'english_audio'  => 'nullable|file|mimes:mpga,mp3|max:10240',
            'image'          => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'english_label.unique' => 'This English label already exists in the vocabulary library.',
        ]);

        $validated['english_label']  = ucfirst(strtolower($validated['english_label']));
        $validated['filipino_label'] = ucfirst($validated['filipino_label']);

        $data = [
            'filipino_label'   => $validated['filipino_label'],
            'english_label'    => $validated['english_label'],
            'category'         => $validated['category'],
            'complexity_level' => $validated['category'] === 'CVC' ? 1 : 2,
            'noun_anchor'      => $validated['english_label'],
            'is_active'        => $request->boolean('is_active'),
        ];

        $filename = $validated['english_label'] . '.mp3';

        if ($request->hasFile('filipino_audio')) {
            $request->file('filipino_audio')->storeAs('audio/filipino', $filename, 'public');
            $data['filipino_audio_url'] = Storage::url('audio/filipino/' . $filename);
        }

        if ($request->hasFile('english_audio')) {
            $request->file('english_audio')->storeAs('audio/english', $filename, 'public');
            $data['english_audio_url'] = Storage::url('audio/english/' . $filename);
        }

        if ($request->hasFile('image')) {
            $label    = strtolower($validated['english_label']);
            $filename = $label . '_' . time() . '.jpg';
            $binary   = file_get_contents($request->file('image')->getRealPath());
            $uploaded = (new SupabaseStorageService)->uploadImage($binary, $filename);
            if ($uploaded) {
                $data['image_url'] = $uploaded;
            }
        }

        $filipinoUrl = $data['filipino_audio_url'] ?? $vocabulary->filipino_audio_url;
        $englishUrl  = $data['english_audio_url']  ?? $vocabulary->english_audio_url;

        if ($filipinoUrl && $englishUrl) {
            $data['audio_status'] = 'Complete';
        } elseif ($filipinoUrl || $englishUrl) {
            $data['audio_status'] = 'Partial';
        } else {
            $data['audio_status'] = 'Missing';
        }

        $vocabulary->update($data);

        $params = array_filter([
            'page'         => $request->input('current_page') ?: null,
            'search'       => $request->input('current_search') ?: null,
            'audio_status' => $request->input('current_audio') ?: null,
            'category'     => $request->input('current_category') ?: null,
            'is_active'    => $request->input('current_status') ?: null,
        ], fn($v) => $v !== null);

        return redirect()->route('admin.vocabulary', $params)
                         ->with('success', 'Vocabulary word updated successfully!');
    }

    public function destroy(VocabularyLibrary $vocabulary)
    {
        $vocabulary->delete();

        return redirect()->route('admin.vocabulary')
                         ->with('success', 'Vocabulary word deleted.');
    }

    public function fetchImageForWord(Request $request)
    {
        $vocabulary = VocabularyLibrary::find($request->input('vocabulary_id'));

        if (!$vocabulary) {
            return response()->json(['success' => false, 'message' => 'Word not found']);
        }

        if ($vocabulary->image_url) {
            return response()->json(['success' => false, 'message' => 'Already has image']);
        }

        $pexelsUrl = (new PexelsService)->fetchImage($vocabulary->english_label);

        if (!$pexelsUrl) {
            return response()->json(['success' => false, 'message' => 'No Pexels result for ' . $vocabulary->english_label]);
        }

        $binary = @file_get_contents($pexelsUrl);

        if (!$binary) {
            return response()->json(['success' => false, 'message' => 'Failed to download image for ' . $vocabulary->english_label]);
        }

        $label    = strtolower($vocabulary->english_label);
        $filename = $label . '_' . time() . '.jpg';
        $imageUrl = (new SupabaseStorageService)->uploadImage($binary, $filename);

        if (!$imageUrl) {
            return response()->json(['success' => false, 'message' => 'Failed to upload image for ' . $vocabulary->english_label]);
        }

        $vocabulary->update(['image_url' => $imageUrl]);

        return response()->json([
            'success'       => true,
            'image_url'     => $imageUrl,
            'english_label' => $vocabulary->english_label,
        ]);
    }
}
