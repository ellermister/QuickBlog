<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = ['name', 'is_show'];

    /**
     * 获取分类列表
     * @return Category[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getList()
    {
        return self::all();
    }

    /**
     * 获取可视分类
     * @return mixed
     */
    public static function getShowList()
    {
        return self::leftJoin("posts",'posts.cat_id','categories.id')
            ->groupBy("cat_id")->select(DB::raw("categories.*"),DB::raw("count(cat_id) as count"))
            ->orderBy('count','desc')
            ->where('categories.is_show', 1)
            ->get();
    }

    /**
     * 新建分类
     * @param $data
     * @return mixed
     */
    public static function newCategoryInstance($data)
    {
        $cat = self::create($data);
        return $cat->save();
    }


    /**
     * 是否显示文本
     * @return string
     */
    public function showText()
    {
        if ($this->is_show) {
            return '显示';
        }
        return '隐藏';
    }
}
