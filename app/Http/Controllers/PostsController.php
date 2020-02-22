<?php

namespace App\Http\Controllers;

use App\Model\Category;
use App\Model\Post;
use Illuminate\Http\Request;

class PostsController extends Controller
{
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

    public function showPostDetail(Request $request, $id)
    {
        $post = Post::getPost($id);
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
