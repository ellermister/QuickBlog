<?php

namespace App\Http\Controllers\Admin;

use App\Model\Platforms;
use App\Model\PostsSchemes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlatformsController extends Controller
{
    /**
     * 显示平台列表
     * @param Request $request
     */
    public function showListPage(Request $request)
    {
        $platforms = Platforms::getList();
        return view('admin.platforms', ['platforms' =>  $platforms]);
    }

    /**
     * 显示账户设置页
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAccountPage(Request $request, $id)
    {
        $platforms = Platforms::where('id',$id)->first();
        return view('admin.platforms.account', ['platforms' => $platforms]);
    }

    /**
     * 更新账户信息
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAccount(Request $request, $id)
    {
        $platforms = Platforms::where('id',$id)->first();
        $data = $request->only(['username', 'password', 'cookie']);
        if($platforms->setAccount($data)){
            return back()->with('message', '新建成功');
        }
        return back()->withErrors(['设置账户失败']);
    }

    /**
     * 创建平台同步计划
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createSchemes(Request $request, $id)
    {
        $platforms = Platforms::where('id',$id)->first();
        $count = PostsSchemes::createSchemes($platforms);
        return back()->with('message', sprintf('已创建%s个同步计划', $count));
    }
}
