<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    /**
     * 显示首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showIndexPage()
    {
        $posts = Post::getListForPage(1);
        $category = Category::getShowList();
        $tags = Post::getTags();
        $data = [
            'posts'    => $posts,
            'category' => $category,
            'tags'     => $tags,
            'tab'      => 'index'
        ];
        return view('index', $data);
    }

    /**
     * 显示最新博文页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLatestPosts()
    {
        $posts = Post::getListForPageWithCondition([]);
        $category = Category::getShowList();
        $tags = Post::getTags();
        $data = [
            'posts'    => $posts,
            'category' => $category,
            'tags'     => $tags,
            'tab'      => 'latest'
        ];
        return view('index', $data);
    }

    /**
     * 显示浏览最多博文页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showHotsPosts()
    {
        $posts = Post::getListForPageWithCondition([], "click");
        $category = Category::getShowList();
        $tags = Post::getTags();
        $data = [
            'posts'    => $posts,
            'category' => $category,
            'tags'     => $tags,
            'tab'      => 'hots'
        ];
        return view('index', $data);
    }

    /**
     * 显示分类下博文
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCategoryPosts(Request $request, $id)
    {
        $posts = Post::getListForPageWithCondition(['cat_id' => $id]);
        $category = Category::getShowList();
        $tags = Post::getTags();
        $data = [
            'posts'    => $posts,
            'category' => $category,
            'tags'     => $tags,
            'tab'      => 'cat_' . $id
        ];
        return view('index', $data);
    }

    /**
     * 显示博文详情
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showPostDetail(Request $request, $id)
    {
        $post = Post::getGuestPostDetail($id);
        $archiveList = Post::getArchiveList();
        $category = Category::getShowList();
        $tags = Post::getTags();
        $data = [
            'post'        => $post,
            'category'    => $category,
            'tags'        => $tags,
            'archiveList' => $archiveList,
            'tab'         => 'cat_' . $post->id
        ];
        return view('post', $data);
    }

    /**
     * 显示时间归档博文列表
     * @param Request $request
     * @param $date
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showArchiveList(Request $request, $date)
    {
        $archiveDate = date('F Y', strtotime($date));
        $posts = Post::getArchivePostsForPage($date);
        if(!$posts->count()){
            abort(404);
        }
        $category = Category::getShowList();
        $tags = Post::getTags();
        $data = [
            'posts'       => $posts,
            'category'    => $category,
            'tags'        => $tags,
            'tab'         => 'archive',
            'archiveDate' => $archiveDate
        ];
        return view('archive', $data);
    }
}
