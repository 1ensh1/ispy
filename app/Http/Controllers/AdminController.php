<?php

namespace App\Http\Controllers;

use App\Mail\ParentAccountCreated;
use App\Services\ActivationService;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    use LogsActivity;
    public function dashboard()
    {
        $totalUsers     = User::count();
        $totalAdmins    = DB::table('administrators')->count();
        $teacherCount   = DB::table('teachers')->count();
        $parentCount    = DB::table('parents')->count();
        $studentCount   = DB::table('students')->count();
        $activeStudents = DB::table('students')->whereNotNull('parent_id')->count();
        $vocabCount     = DB::table('vocabulary_library')->where('is_active', true)->count();

        $weekStart    = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $activityRows = DB::table('student_progress')
            ->selectRaw('DATE(attempted_at) as day, SUM(attempts) as total')
            ->where('attempted_at', '>=', $weekStart)
            ->where('attempted_at', '<', $weekStart->copy()->addDays(7))
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $weeklyData = [];
        for ($i = 0; $i < 7; $i++) {
            $date         = $weekStart->copy()->addDays($i)->toDateString();
            $weeklyData[] = $activityRows->has($date) ? (int) $activityRows->get($date)->total : 0;
        }
        $weeklyActivityJson = json_encode($weeklyData);

        $recentActivity = DB::table('notifications')
            ->where('recipient_role', 'Admin')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'totalAdmins', 'teacherCount', 'parentCount', 'studentCount',
            'activeStudents', 'vocabCount', 'weeklyActivityJson', 'recentActivity'
        ));
    }

    public function users(\Illuminate\Http\Request $request)
    {
        return redirect()->route('admin.teachers.index', array_filter([
            'tab'    => $request->query('tab'),
            'search' => $request->query('search'),
        ]));
    }

    public function update(\Illuminate\Http\Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        $user->save();

        $redirectTab = $user->role === 'parent' ? 'parents' : $user->role;
        return redirect()->route('admin.teachers.index', ['tab' => $redirectTab])
                         ->with('success', "Account for \"{$user->name}\" updated successfully.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        $role = $user->role;
        $name = $user->name;

        if ($role === 'admin') {
            DB::table('administrators')->where('user_id', $user->id)->delete();
        } elseif ($role === 'teacher') {
            DB::table('teachers')->where('user_id', $user->id)->delete();
        } elseif ($role === 'parent') {
            $parentRecord = DB::table('parents')->where('user_id', $user->id)->first();
            if ($parentRecord) {
                $linkedStudents = DB::table('students')->where('parent_id', $parentRecord->id)->count();
                if ($linkedStudents > 0) {
                    return redirect()->route('admin.teachers.index', ['tab' => 'parents'])
                        ->with('error', "Cannot delete \"{$name}\". They have active student(s) linked to their account. Please reassign the student(s) first.");
                }
                DB::table('parents')->where('user_id', $user->id)->delete();
            }
        }

        $user->delete();

        $redirectTab = $role === 'parent' ? 'parents' : $role;
        return redirect()->route('admin.teachers.index', ['tab' => $redirectTab])
                         ->with('success', "Account for \"{$name}\" has been deleted.");
    }

    public function store(\Illuminate\Http\Request $request)
    {
        // 1. Validate the incoming form data
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users',
            'password'       => 'required_if:role,admin|nullable|string|min:6',
            'role'           => 'required|in:admin,parent',
            'contact_number' => 'nullable|string|max:20',
        ]);

        // 2. Create the core User record. Admins set their own password here;
        //    parents get a generated one that is revealed only after activation.
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['role'] === 'parent'
                ? bcrypt(Str::random(10))
                : bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        // 3. Link them to their specific role table based on ERD
        if ($validated['role'] === 'admin') {
            \Illuminate\Support\Facades\DB::table('administrators')->insert(['user_id' => $user->id]);
        } elseif ($validated['role'] === 'teacher') {
            \Illuminate\Support\Facades\DB::table('teachers')->insert(['user_id' => $user->id]);
        } elseif ($validated['role'] === 'parent') {
            $parentId = \Illuminate\Support\Facades\DB::table('parents')->insertGetId([
                'user_id'        => $user->id,
                'name'           => $validated['name'],
                'contact_number' => $validated['contact_number'] ?? null,
                'status'         => 'Inactive',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Issue a one-time activation token and email the activation link.
            $tokenData = app(ActivationService::class)->generateToken();
            DB::table('parent_activation_tokens')->insert([
                'parent_id'  => $parentId,
                'token'      => $tokenData['token'],
                'created_at' => now(),
                'expires_at' => $tokenData['expires_at'],
            ]);
            $activationUrl = route('parent.activate', ['token' => $tokenData['token']]);

            try {
                Mail::to($user->email)->send(new ParentAccountCreated($user->name, $user->email, $activationUrl));
            } catch (\Throwable $e) {
                Log::error("Failed to send parent account email to {$user->email}: {$e->getMessage()}");
            }
        }

        if ($validated['role'] === 'parent') {
            self::log('create', "created parent account for {$user->name}");
        }

        $redirectTab = $validated['role'] === 'parent' ? 'parents' : $validated['role'];
        return redirect()->route('admin.teachers.index', ['tab' => $redirectTab])
                         ->with('success', 'User account created successfully!');
    }

    public function resendParentActivation(User $parent)
    {
        $record = DB::table('parents')->where('user_id', $parent->id)->first();

        if (!$record) {
            return redirect()->route('admin.teachers.index', ['tab' => 'parents'])
                ->with('error', "Parent record not found for \"{$parent->name}\".");
        }

        // Replace any existing token so only the newest link works.
        DB::table('parent_activation_tokens')->where('parent_id', $record->id)->delete();

        $tokenData = app(ActivationService::class)->generateToken();
        DB::table('parent_activation_tokens')->insert([
            'parent_id'  => $record->id,
            'token'      => $tokenData['token'],
            'created_at' => now(),
            'expires_at' => $tokenData['expires_at'],
        ]);
        $activationUrl = route('parent.activate', ['token' => $tokenData['token']]);

        try {
            Mail::to($parent->email)->send(new ParentAccountCreated($parent->name, $parent->email, $activationUrl));
        } catch (\Throwable $e) {
            Log::error("Failed to resend parent activation email to {$parent->email}: {$e->getMessage()}");
        }

        self::log('update', "resent activation email to parent {$parent->name}");

        return redirect()->route('admin.teachers.index', ['tab' => 'parents'])
            ->with('success', "Activation email resent to {$parent->email}.");
    }
}