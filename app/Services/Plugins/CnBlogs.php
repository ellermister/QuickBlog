<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/2/22
 * Time: 23:53
 */

namespace App\Services\Plugins;


use App\Model\PostsSchemes;
use App\Services\Plugin;

class CnBlogs extends Plugin
{
    protected $name = "cnblogs";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "博客园";
    protected $describe = "博客园是一个面向开发者的知识分享社区。程序员问答社区，解决程序员的技术难题。";
    protected $img = "cnblogs-logo.gif";

    /**
     * 插件安装时信息
     * @return array
     */
    function installInfo()
    {
        return [
            'author'   => $this->author,
            'name'     => $this->name,
            'title'    => $this->title,
            'describe' => $this->describe,
            'img'      => $this->img
        ];
    }

    function categoryList()
    {
        // TODO: Implement categoryList() method.
        return [];
    }

    public function updateScheme(PostsSchemes $postsScheme)
    {
        // TODO: Implement updateScheme() method.
        return "";
    }

}