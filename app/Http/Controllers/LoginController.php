<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    public function showLoginPage()
    {
        return view('login');
    }

    public function verifyLogin(Request $request)
    {
        $input = $request->only(['email','password']);
        if(Auth::attempt($input)){
            return redirect()->route('dashboard');
        }
        return redirect()->back()->withErrors(['error' => '用户名或者密码错误哦'])->withInput();;
    }
}
