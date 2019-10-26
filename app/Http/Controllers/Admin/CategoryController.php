<?php

namespace App\Http\Controllers\Admin;

use App\Model\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * 显示分类列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showListPage()
    {
        $category = Category::getList();
        return view('admin.category', ['category' => $category]);
    }

    /**
     * 分类编辑页
     */
    public function showEditorPage()
    {
        return view('admin.category.editor');
    }


    /**
     * 新建分类
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function newCategoryInstance(Request $request)
    {
        $data = $request->only(['name', 'is_show']);
        if (isset($data['is_show'])) {
            $data['is_show'] = 1;
        }
        if (Category::newCategoryInstance($data)) {
            return redirect('/admin/category')->with('message', '新建成功');
        }
        return redirect('/admin/category')->withErrors(['新建分类失败']);
    }

    /**
     * 更新分类编辑页
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function showEditorPageWithCategory(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return abort(404);
        }
        return view('admin.category.editor', ['category' => $category]);
    }

    /**
     * 更新分类实例
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function updateCategoryInstance(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return abort(404);
        }

        $data = $request->only(['name', 'is_show']);

        $data['is_show'] = ($data['is_show'] ?? 0) ? 1 : 0;
        if ($category->update($data)) {
            return back()->with('message', '新建成功');
        }
        return back()->withErrors(['更新分类失败']);
    }

}
