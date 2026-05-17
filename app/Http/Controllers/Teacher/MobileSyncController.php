<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;

class MobileSyncController extends Controller
{
    public function index()
    {
        return view('teacher.mobile-sync');
    }
}
