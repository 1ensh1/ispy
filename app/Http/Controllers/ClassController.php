<?php

namespace App\Http\Controllers;

use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use App\Models\ClassList;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    use LogsActivity;
    public function generatePin(Request $request, ClassList $classList)
    {
        do {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (DB::table('class_lists')->where('unified_classroom_pin', $pin)->exists());

        $classList->unified_classroom_pin = $pin;
        $classList->save();

        self::log('create', "generated PIN for class '{$classList->class_name}'");

        if ($request->filled('teacher_id')) {
            return redirect()->route('admin.teachers.profile', ['teacher' => $request->input('teacher_id')])
                ->with('new_class_pin', $pin)
                ->with('new_class_name', $classList->class_name);
        }

        return redirect()->route('admin.teachers.index')
            ->with('new_class_pin', $pin)
            ->with('new_class_name', $classList->class_name);
    }
}
