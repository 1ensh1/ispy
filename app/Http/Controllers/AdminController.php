<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Pull REAL counts from the database
        $totalUsers = User::count();
        $teacherCount = User::where('role', 'teacher')->count();
        $parentCount = User::where('role', 'parent')->count();
        $studentCount = DB::table('students')->count();

        $vocabCount = 100; 

        $recentLogs = [
            ['user' => auth()->user()->name, 'action' => 'Accessed Admin Portal', 'time' => 'Just now'],
            ['user' => 'System', 'action' => 'Database connection active', 'time' => '1 min ago'],
        ];

        return view('admin.dashboard', compact(
            'totalUsers', 'teacherCount', 'parentCount', 'studentCount', 'vocabCount', 'recentLogs'
        ));
    }

    public function users(\Illuminate\Http\Request $request)
    {
        $activeTab = $request->query('tab', 'teacher');
        $search    = $request->query('search');

        if ($activeTab === 'teacher') {
            return redirect()->route('admin.teachers.index', array_filter(['search' => $search]));
        }

        $query = User::where('role', $activeTab);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->appends([
            'tab'    => $activeTab,
            'search' => $search,
        ]);

        $extraData          = [];
        $studentCountsByUser = [];

        if ($activeTab === 'parent') {
            $userIds = $users->pluck('id')->toArray();

            $parentRecords = DB::table('parents')
                ->whereIn('user_id', $userIds)
                ->get()
                ->keyBy('user_id');

            $parentIds = $parentRecords->pluck('id')->toArray();

            $childrenByParent = DB::table('students')
                ->whereIn('parent_id', $parentIds)
                ->get()
                ->groupBy('parent_id');

            foreach ($users as $user) {
                $parentRecord = $parentRecords->get($user->id);
                $parentId     = $parentRecord ? $parentRecord->id : null;
                $extraData[$user->id] = [
                    'children' => ($parentId && $childrenByParent->has($parentId))
                        ? $childrenByParent->get($parentId)
                        : collect(),
                ];
            }
        }

        return view('admin.users', compact('users', 'activeTab', 'search', 'extraData', 'studentCountsByUser'));
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

        return redirect()->route('admin.users', ['tab' => $user->role])
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
                    return redirect()->route('admin.users', ['tab' => 'parent'])
                        ->with('error', "Cannot delete \"{$name}\". They have active student(s) linked to their account. Please reassign the student(s) first.");
                }
                DB::table('parents')->where('user_id', $user->id)->delete();
            }
        }

        $user->delete();

        return redirect()->route('admin.users', ['tab' => $role])
                         ->with('success', "Account for \"{$name}\" has been deleted.");
    }

    public function store(\Illuminate\Http\Request $request)
    {
        // 1. Validate the incoming form data
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users',
            'password'       => 'required|string|min:6',
            'role'           => 'required|in:admin,parent',
            'contact_number' => 'nullable|string|max:20',
        ]);

        // 2. Create the core User record
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        // 3. Link them to their specific role table based on ERD
        if ($validated['role'] === 'admin') {
            \Illuminate\Support\Facades\DB::table('administrators')->insert(['user_id' => $user->id]);
        } elseif ($validated['role'] === 'teacher') {
            \Illuminate\Support\Facades\DB::table('teachers')->insert(['user_id' => $user->id]);
        } elseif ($validated['role'] === 'parent') {
            \Illuminate\Support\Facades\DB::table('parents')->insert([
                'user_id'        => $user->id,
                'name'           => $validated['name'],
                'contact_number' => $validated['contact_number'] ?? null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        // 4. Redirect back with a success message, switching to their newly assigned tab
        return redirect()->route('admin.users', ['tab' => $validated['role']])
                         ->with('success', 'User account created successfully!');
    }
}