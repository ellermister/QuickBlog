<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = ['title', 'keywords', 'description', 'contents', 'post_img', 'category', 'cat_id', 'is_show'];


    /**
     * 新建博文
     * @param array $data
     * @return mixed
     */
    public static function newPostInstance(array $data)
    {
        $post = self::create($data);
        return $post->save();
    }

    /**
     * 获取博文列表(含分页)
     * @return mixed
     */
    public static function getListForPage()
    {
        return self::orderBy('created_at', 'DESC')->paginate(15);
    }

    /**
     * 显示文本
     * @return string
     */
    public function showText()
    {
        if ($this->is_show) {
            return '显示';
        }
        return '隐藏';
    }

    /**
     * 获取博文
     * @param $id
     * @return mixed
     */
    public static function getPost($id)
    {
        return self::find($id);
    }
}
