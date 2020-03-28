<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function showPage()
    {
        $data = [
            'tab' => 'about',
        ];
        return view('about', $data);
    }
}
