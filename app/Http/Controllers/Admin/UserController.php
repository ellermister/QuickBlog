<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function showUserListPage()
    {
        $users = User::query()->paginate(12);
        return view('admin.users', ['users' => $users]);
    }

    public function showUserProfile(int $id)
    {
        $user = User::query()->find($id);
        if (!$user) {
            abort(404);
        }

        $currentUser = Auth::user();
        if(!$currentUser->isAdmin() && $currentUser->id != $user->id){
            abort(403);
        }

        return view('admin.user', ['user_info' => $user]);
    }

    public function showNewUserPage()
    {
        return view('admin.user');
    }

    public function newUserInstance(Request $request)
    {
        $data = $request->only([
            'name', 'email', 'password', 'avatar',
        ]);

        if ($ret = User::newUserInstance($data)) {
            return redirect('/admin/user')->with('message', '新建成功');
        }
        return redirect('/admin/user')->withErrors(['新建用户失败']);
    }

    public function updateUser(Request $request, int $id)
    {
        $currentUser = Auth::user();
        if(!$currentUser->isAdmin() && $currentUser->id != $id){
            abort(403);
        }
        if (User::updateUser($id, $request->only(['email', 'name', 'password', 'avatar']))) {
            return back()->with('message', '更新用户成功');
        }
        return back()->withErrors(['更新用户失败']);

    }
}
