<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassList;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function generatePin(Request $request, ClassList $classList)
    {
        do {
            $pin = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (DB::table('class_lists')->where('unified_classroom_pin', $pin)->exists());

        $classList->unified_classroom_pin = $pin;
        $classList->save();

        return redirect()->route('admin.teachers.index')
            ->with('new_class_pin', $pin)
            ->with('new_class_name', $classList->class_name);
    }
}
