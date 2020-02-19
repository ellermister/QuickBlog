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
        ];
        return view('index', $data);
    }
}
