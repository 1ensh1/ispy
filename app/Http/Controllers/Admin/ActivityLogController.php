<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        if ($request->filled('action') && $request->action !== 'all') {
            if ($request->action === 'Assign') {
                $query->where('action', 'ILIKE', 'Assign%');
            } else {
                $query->where('action', 'ILIKE', '%' . $request->action . '%');
            }
        }
        if ($request->filled('search')) {
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($request->search) . '%']);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50], true)) {
            $perPage = 10;
        }

        $logs = $query->paginate($perPage)->appends(request()->query());

        return view('admin.activity_logs.index', compact('logs', 'perPage'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = ActivityLog::orderByDesc('created_at');

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }
        if ($request->filled('action') && $request->action !== 'all') {
            if ($request->action === 'Assign') {
                $query->where('action', 'ILIKE', 'Assign%');
            } else {
                $query->where('action', 'ILIKE', '%' . $request->action . '%');
            }
        }
        if ($request->filled('search')) {
            $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($request->search) . '%']);
        }

        $filename = 'activity-logs-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date/Time', 'Role', 'Action', 'Description']);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->created_at?->format('M d, Y H:i') ?? '',
                        $log->role ?? '',
                        $log->action ?? '',
                        $log->description ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
