<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        if ($request->filled('action') && $request->action !== 'all') {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($request->search) . '%']);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.activity_logs.index', compact('logs'));
    }
}
