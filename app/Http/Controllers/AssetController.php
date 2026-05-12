<?php

namespace App\Http\Controllers;

use App\Models\VocabularyLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $words = VocabularyLibrary::latest()->paginate(25);

        $totalUploaded = VocabularyLibrary::whereNotNull('filipino_audio_url')->count()
                       + VocabularyLibrary::whereNotNull('english_audio_url')->count();
        $totalWords    = VocabularyLibrary::count();
        $totalMissing  = ($totalWords * 2) - $totalUploaded;

        return view('admin.assets', compact('words', 'totalUploaded', 'totalMissing'));
    }

    public function upload(Request $request, VocabularyLibrary $vocabulary)
    {
        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,mpeg,wav,ogg|max:10240',
            'language'   => 'required|in:filipino,english',
        ]);

        $field = $request->language === 'filipino' ? 'filipino_audio_url' : 'english_audio_url';

        // Delete old file if one exists
        if ($vocabulary->$field) {
            Storage::disk('public')->delete($vocabulary->$field);
        }

        $path = $request->file('audio_file')->store('audio', 'public');
        $vocabulary->update([$field => $path]);

        // Sync the has_audio flag
        $vocabulary->refresh();
        $vocabulary->update([
            'has_audio' => !is_null($vocabulary->filipino_audio_url)
                        && !is_null($vocabulary->english_audio_url),
        ]);

        return redirect()->route('admin.assets')
                         ->with('success', "Audio uploaded for \"{$vocabulary->{''.($request->language === 'filipino' ? 'filipino' : 'english').'_label'}}\".");
    }

    public function destroy(VocabularyLibrary $vocabulary, string $language)
    {
        abort_unless(in_array($language, ['filipino', 'english']), 404);

        $field = "{$language}_audio_url";

        if ($vocabulary->$field) {
            Storage::disk('public')->delete($vocabulary->$field);
            $vocabulary->update([$field => null, 'has_audio' => false]);
        }

        return redirect()->route('admin.assets')->with('success', 'Audio file removed.');
    }
}
