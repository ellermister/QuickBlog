<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HelperController extends Controller
{
    public function showInfo(Request $request)
    {
        return view('admin.helper');
    }
}
