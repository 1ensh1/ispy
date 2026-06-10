<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VocabularySuggestion;
use App\Models\VocabularyLibrary;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VocabularySuggestionsController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = VocabularySuggestion::with('teacher')
            ->orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END")
            ->orderBy('submitted_at', 'desc');

        if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $query->where('status', $status);
        }

        $suggestions = $query->paginate(20)->withQueryString();

        return view('admin.vocabulary-suggestions.index', compact('suggestions', 'status'));
    }

    public function approve(VocabularySuggestion $suggestion)
    {
        if ($suggestion->status !== 'Pending') {
            return back()->with('error', 'This suggestion has already been reviewed.');
        }

        if (VocabularyLibrary::where('english_label', $suggestion->english_label)->exists()) {
            return back()->with('error', "Cannot approve: \"{$suggestion->english_label}\" already exists in the vocabulary library.");
        }

        VocabularyLibrary::create([
            'noun_anchor'      => $suggestion->english_label,
            'english_label'    => $suggestion->english_label,
            'filipino_label'   => $suggestion->filipino_label,
            'category'         => $suggestion->category,
            'audio_status'     => 'Missing',
            'is_active'        => true,
            'complexity_level' => 1,
        ]);

        $suggestion->update([
            'status'      => 'Approved',
            'reviewed_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $suggestion->teacher_id,
            'recipient_role'    => 'Teacher',
            'notification_type' => 'Suggestion',
            'action_url'        => route('teacher.vocabulary'),
            'title'             => 'Vocabulary Suggestion Approved',
            'message'           => "Your suggestion \"{$suggestion->english_label}\" ({$suggestion->filipino_label}) has been approved and added to the vocabulary library.",
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        self::log('approve', "approved vocabulary suggestion '{$suggestion->english_label}'");

        return back()->with('success', "Suggestion \"{$suggestion->english_label}\" approved and added to the vocabulary library.");
    }

    public function reject(VocabularySuggestion $suggestion)
    {
        if ($suggestion->status !== 'Pending') {
            return back()->with('error', 'This suggestion has already been reviewed.');
        }

        $suggestion->update([
            'status'      => 'Rejected',
            'reviewed_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'recipient_id'      => $suggestion->teacher_id,
            'recipient_role'    => 'Teacher',
            'notification_type' => 'Suggestion',
            'action_url'        => route('teacher.vocabulary'),
            'title'             => 'Vocabulary Suggestion Rejected',
            'message'           => "Your suggestion \"{$suggestion->english_label}\" ({$suggestion->filipino_label}) was not approved at this time.",
            'is_read'           => false,
            'created_at'        => now(),
        ]);

        self::log('reject', "rejected vocabulary suggestion '{$suggestion->english_label}'");

        return back()->with('success', "Suggestion \"{$suggestion->english_label}\" has been rejected.");
    }
}
