<?php

namespace App\Http\Controllers\Admin;

use App\Model\Category;
use App\Model\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Parsedown;
use League\HTMLToMarkdown\HtmlConverter;
use Illuminate\Support\Facades\Storage;

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
            'title', 'cat_id', 'contents', 'is_show', 'is_sync', 'keywords', 'description'
        ]);

        $data['is_show'] = ($data['is_show'] ?? 0) ? 1 : 0;
        $data['is_sync'] = ($data['is_sync'] ?? 0) ? 1 : 0;
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
            'title', 'cat_id', 'contents', 'is_show', 'is_sync', 'keywords', 'description'
        ]);

        $data['is_show'] = ($data['is_show'] ?? 0) ? 1 : 0;
        $data['is_sync'] = ($data['is_sync'] ?? 0) ? 1 : 0;
        if ($post->update($data)) {
            return back()->with('message', '新建成功');
        }
        return back()->withErrors(['更新博文失败']);
    }

    /**
     * 删除文章实例及文章关联
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function deletePostInstance(Request $request, $id)
    {
        $post = Post::getPost($id);
        if (!$post) {
            return abort(404);
        }

        if ($post->deleteAndUnion()) {
            return response('', 200);
        }
        return response(eeJson("删除失败", 500), 500);
    }

    /**
     * 设置精选/取消精选
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function ActiveFeatured(Request $request, $id)
    {
        $post = Post::getPost($id);
        if (!$post) {
            return abort(404);
        }
        if ($post->featured == 0) {
            $post->featured = time();
        } else {
            $post->featured = 0;
        }
        $post->save();
        return redirect()->back();
    }

    /**
     * 上传图片文件
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function uploadImage(Request $request)
    {
        $allow_types = ['image/png', 'image/jpeg', 'image/gif', 'image/jpg'];
        $picture = $request->file('editormd-image-file');

        if ($request->hasFile('editormd-image-file') && $picture->isValid()) {

            if (!in_array($picture->getMiMeType(), $allow_types)) {
                return response($this->outputImg('图片类型不正确'));
            }

            if ($picture->getClientSize() > 1024 * 1024 * 6) {
                return response($this->outputImg('图片大小不能超过 6M'));
            }

            $path = $picture->store('public/images');

            $path = Storage::url($path);
            return response($this->outputImg('ok', 1, $path, asset($path)));
        } else {
            return response($this->outputImg('无效上传'));
        }
    }

    private function outputImg($message, $status = 0, $fileName = '', $url = '')
    {
        $data = [
            'success'  => $status ? 1 : 0,
            'message'  => $message,
            'fileName' => $fileName,
            'url'      => $url
        ];
        return json_encode($data);
    }

}
