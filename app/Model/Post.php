<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = ['title', 'keywords', 'description', 'contents', 'post_img', 'category', 'cat_id', 'is_show','is_sync'];


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
        if (!is_null($isShow)) {
            return self::where('is_show', $isShow)->orderBy('created_at', 'DESC')->paginate(15);
        }
        return self::orderBy('created_at', 'DESC')->paginate(15);
    }

    /**
     * 通过条件获取博文列表含分页
     * @param array $condition
     * @param string $order
     * @return mixed
     */
    public static function getListForPageWithCondition(array $condition, $order = "created_at")
    {
        if(Arr::isAssoc($condition)){
        }
        return self::where("is_show", 1)->where($condition)->orderBy($order, 'DESC')->paginate(15);
    }

    /**
     * 获取最近的博文
     * @param int $limit
     * @return mixed
     */
    public static function getRecentPosts($limit = 3)
    {
        return self::where("is_show", 1)->orderBy("created_at", 'DESC')->limit($limit)->get();
    }

    /**
     * 获取阅读最多的博文
     * @param int $limit
     * @return mixed
     */
    public static function getMostRead($limit = 5)
    {
        return self::where("is_show", 1)->orderBy("click", 'DESC')->limit($limit)->get();
    }

    /**
     * 获取精选文章
     * @param int $limit
     * @return mixed
     */
    public static function getFeaturedPosts($limit = 2)
    {
        // 精选按最新加入精选的时间排序
        return self::where("is_show", 1)->where('featured', '>', 0)->orderBy("featured", 'DESC')->limit($limit)->get();
    }

    /**
     * 获取归档文章含分页
     * @param $dateText
     * @return mixed
     */
    public static function getArchivePostsForPage($dateText)
    {
        $dateBegin = strtotime(date('Y-m-1 0:0:0', strtotime($dateText)));
        $dateEnd = strtotime('+1 month', strtotime($dateText));
        return self::where("is_show", 1)->where('created_at', '>=', $dateBegin)->where('created_at', '<', $dateEnd)
            ->orderBy("featured", 'DESC')->paginate(15);
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
     * 获取访客博文详情并增加阅览
     * @param $id
     * @return mixed
     */
    public static function getGuestPostDetail($id)
    {
        $post = self::getPost($id);
        if ($post) {
            $post->click++;
            $post->save();
        }
        return $post;
    }

    /**
     * 获取标签
     * @return array
     */
    public static function getTags()
    {
        $list = self::select('keywords')->get();
        $words = [];
        foreach ($list as $item) {
            $words = array_merge($words, explode(',', $item['keywords']));
        }
        return $words;
    }

    /**
     * 获取归档列表
     * @return array
     */
    public static function getArchiveList()
    {
        $buffer = self::select(DB::raw('FROM_UNIXTIME(created_at,"%M %X") as date'))->groupBy("date")->get();
        return $buffer;
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
        return "cat-" . strval($value + 1);
    }

    /**
     * 获取缩略图
     */
    public function getThumbnail()
    {
        if (preg_match('/\!\[[^\]]*\]\(([^\)]+)\)/is', $this->contents, $result)) {
            if (isset($result[1])) {
                if (preg_match('/([\S]+)\s/is', $result[1], $img)) {
                    return $img[1];
                }
                return $result[1];
            }
        }
        return '';
    }

    /**
     * 获取分类名称
     * @return string
     */
    public function getCateName()
    {
        $cat = Category::where('id', $this->cat_id)->first();
        if ($cat) {
            return $cat->name;
        }
        return "默认分类";
    }

    /**
     * 获取Markdown内容
     * @return mixed
     */
    public function getMarkdownBody()
    {
        return $this->contents;
    }

    /**
     * 获取HTML富文本内容
     * @return string
     */
    public function getHtmlBody()
    {
        $Parsedown = new \Parsedown();
        return $Parsedown->text($this->contents);
    }

    /**
     * 删除文章及关联
     * @return bool
     */
    public function deleteAndUnion()
    {
        $ret = DB::transaction(function (){
            if($this->delete() && PostsSchemes::where('post_id', $this->id)->delete()){
                return true;
            }
            return false;
        });
        return $ret ? true :false;
    }


}
