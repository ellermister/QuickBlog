<?php

namespace App\Http\Controllers\Admin;

use App\Model\Category;
use App\Model\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Parsedown;
use League\HTMLToMarkdown\HtmlConverter;

class PostController extends Controller
{

    /**
     * 显示编辑器页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showEditorPage()
    {
        $category = Category::getList();
        return view('admin.post', ['category' => $category]);
    }

    /**
     * 新建博文
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function newPostInstance(Request $request)
    {
        $data = $request->only([
            'title', 'cat_id', 'contents', 'is_show', 'keywords', 'description'
        ]);

        $data['is_show'] = ($data['is_show'] ?? 0) ? 1 : 0;
        if ($ret = Post::newPostInstance($data)) {
            return redirect('/admin/post')->with('message', '新建成功');
        }
        return redirect('/admin/post')->withErrors(['新建博文失败']);
    }

    /**
     * 显示博文列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View1
     */
    public function showPostList(Request $request)
    {
        $posts = Post::getListForPage();
        return view('admin.posts', ['posts' => $posts]);
    }

    /**
     * 显示博文编辑页
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function showEditorPageWithPost(Request $request, $id)
    {
        $post = Post::getPost($id);
        if (!$post) {
            return abort(404);
        }
        $category = Category::getList();
        return view('admin.post', ['post' => $post, 'category' => $category]);
    }

    /**
     * 更新博文
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function updatePostInstance(Request $request, $id)
    {
        $post = Post::getPost($id);
        if (!$post) {
            return abort(404);
        }

        $data = $request->only([
            'title', 'cat_id', 'contents', 'is_show', 'keywords', 'description'
        ]);

        $data['is_show'] = ($data['is_show'] ?? 0) ? 1 : 0;
        if ($post->update($data)) {
            return back()->with('message', '新建成功');
        }
        return back()->withErrors(['更新博文失败']);
    }

}
