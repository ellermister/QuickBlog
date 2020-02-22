<?php

/**
 * eeJSON
 * @param $message
 * @param $code
 * @param null $data
 * @return false|string
 */
function eeJson($message, $code, $data = null)
{
    $format = [
        'response' => [
            'message' => $message,
            'code'    => $code,
        ],
        'data'     => $data
    ];
    return json_encode($format);
}

/**
 * 获取分类列表
 * @return mixed
 */
function getCateList($limit = 4)
{
    return \App\Model\Category::getShowList($limit);
}

/**
 * 获取最近的博文
 * @param int $limit
 * @return mixed
 */
function getRecentPosts($limit = 3)
{
    return \App\Model\Post::getRecentPosts($limit);
}
/**
 * 获取阅读最多的博文
 * @param int $limit
 * @return mixed
 */
function getMostRead($limit = 4)
{
    return \App\Model\Post::getMostRead($limit);
}

/**
 * 获取精选的文章
 * @param int $limit
 * @return mixed
 */
function getFeaturedPosts($limit = 2)
{
    return \App\Model\Post::getFeaturedPosts($limit);
}