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
    public static function getListForPage($isShow = null)
    {
        if(!is_null($isShow)){
            return self::where('is_show', $isShow)->orderBy('created_at', 'DESC')->paginate(15);
        }
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

    /**
     * 获取标签
     * @return array
     */
    public static function getTags()
    {
        $list = self::select('keywords')->get();
        $words = [];
        foreach($list as $item){
            $words = array_merge($words, explode(',', $item['keywords']));
        }
        return $words;
    }

    /**
     * 获取日期格式文本
     * @return mixed
     */
    public function getDateText()
    {
        return $this->created_at->format('F d,Y');
    }

    /**
     * 获取分类的颜色CLASS
     * @return string
     */
    public function getCatClass()
    {
        $value = $this->cat_id % 4;
        return "cat-".strval($value+1);
    }

    /**
     * 获取缩略图
     */
    public function getThumbnail(){
        if(preg_match('/\!\[[^\]]*\]\(([^\)]+)\)/is', $this->contents, $result)){
            if(isset($result[1])){
                return $result[1];
            }
        }
        return '';
    }

}
