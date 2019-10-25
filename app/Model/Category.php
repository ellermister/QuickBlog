<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $dateFormat = 'U';

    /**
     * 获取分类列表
     * @return Category[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getList()
    {
        return self::all();
    }
}
