<?php

namespace App\Http\Controllers\Admin;

use App\Model\CategoryUnion;
use App\Model\Platforms;
use App\Model\PostsSchemes;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Category;

class PlatformsController extends Controller
{
    /**
     * 显示平台列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
        $category = Category::getList();
        return view('admin.platforms.account', ['platforms' => $platforms, 'category' => $category]);
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

    /**
     * 创建关联分类
     * @param Request $request
     * @return false|string
     */
    public function createUnionCategory(Request $request, $id)
    {
        $data = $request->only([ 'site_cat_id', 'platform_cat_id', 'platform_cat_name']);
        $data['platform_id'] = $id;
        if(CategoryUnion::createUnionCategory($data))
        {
            return eeJson('关联成功', 200);
        }
        return eeJson('关联失败', 500);
    }

    /**
     * 获取关联分类列表
     * @param Request $request
     * @param $id
     * @return false|string
     */
    public function getUnionCategoryList(Request $request, $id)
    {
        $category = CategoryUnion::getCategoryListForPlatform($id);
        return view('admin.layouts.category',['category' => $category])->render();
    }
}
