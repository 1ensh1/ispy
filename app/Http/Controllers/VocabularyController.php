<?php

namespace App\Http\Controllers;

use App\Models\VocabularyLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VocabularyController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->query('search');
        $audioFilter  = $request->query('audio_status');
        $activeFilter = $request->query('is_active');

        $query = VocabularyLibrary::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('filipino_label', 'like', "%{$search}%")
                  ->orWhere('english_label', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($audioFilter && in_array($audioFilter, ['Complete', 'Partial', 'Missing'])) {
            $query->where('audio_status', $audioFilter);
        }

        if ($activeFilter === '1') {
            $query->where('is_active', 1);
        } elseif ($activeFilter === '0') {
            $query->where('is_active', 0);
        }

        $words = $query->latest()->paginate(15)->appends([
            'search'       => $search,
            'audio_status' => $audioFilter,
            'is_active'    => $activeFilter,
        ]);

        return view('admin.vocabulary', compact('words', 'search', 'audioFilter', 'activeFilter'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filipino_label'  => 'required|string|max:255',
            'english_label'   => 'required|string|max:255',
            'category'        => 'required|string|max:255',
            'complexity_level'=> 'required|in:1,2',
        ]);

        $validated['noun_anchor']  = $validated['english_label'];
        $validated['audio_status'] = 'Missing';
        $validated['is_active']    = true;

        VocabularyLibrary::create($validated);

        return redirect()->route('admin.vocabulary', ['search' => $request->query('search')])
                         ->with('success', 'Vocabulary word added successfully!');
    }

    public function update(Request $request, VocabularyLibrary $vocabulary)
    {
        $validated = $request->validate([
            'filipino_label'   => 'required|string|max:255',
            'english_label'    => 'required|string|max:255',
            'category'         => 'required|string|max:255',
            'complexity_level' => 'required|in:1,2',
            'is_active'        => 'nullable|boolean',
            'filipino_audio'   => 'nullable|file|mimes:mpga,mp3|max:10240',
            'english_audio'    => 'nullable|file|mimes:mpga,mp3|max:10240',
        ]);

        $data = [
            'filipino_label'  => $validated['filipino_label'],
            'english_label'   => $validated['english_label'],
            'category'        => $validated['category'],
            'complexity_level'=> $validated['complexity_level'],
            'noun_anchor'     => $validated['english_label'],
            'is_active'       => $request->boolean('is_active'),
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

        return redirect()->route('admin.vocabulary', ['search' => $request->query('search')])
                         ->with('success', 'Vocabulary word updated successfully!');
    }

    public function destroy(VocabularyLibrary $vocabulary)
    {
        $vocabulary->delete();

        return redirect()->route('admin.vocabulary')
                         ->with('success', 'Vocabulary word deleted.');
    }
}
