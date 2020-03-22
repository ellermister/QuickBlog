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

/**
 * 获取系统设置
 * @param null $name
 * @param string $default
 * @return array
 */
function getSettings($name = null,$default = "")
{
    static $data;
    if(!$data){
        $data = \App\Model\Setting::getSetting();
    }
    if(is_null($name)){
        return $data;
    }
    return $data->get($name, $default);
}

/**
 * 获取分类名称
 * @param $catId
 * @param string $default
 * @return string
 */
function getCategoryName($catId, $default = "")
{
    return \App\Model\Category::getCategoryName($catId, $default);
}