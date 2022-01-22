<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
            $user = Auth::user();
            $user->last_ip = $request->getClientIp();
            $user->last_time = time();
            $user->save();
            return redirect()->route('dashboard');
        }
        return redirect()->back()->withErrors(['error' => '用户名或者密码错误哦'])->withInput();;
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
