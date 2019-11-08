<?php
namespace App\Services;


use App\Model\Post;
use App\Services\Plugin;

class OSChina implements Plugin
{
    public function __sleep()
    {
        // TODO: Implement __sleep() method.
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    public function sendPost(Post $post)
    {
        // TODO: Implement sendPost() method.
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
    }

    public function updatePost(Post $post)
    {
        // TODO: Implement updatePost() method.
    }

    public function getPost(Post $post): int
    {
        // TODO: Implement getPost() method.
    }
}