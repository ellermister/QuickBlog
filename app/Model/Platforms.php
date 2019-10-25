<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Platforms extends Model
{
    /**
     * 获取平台
     * @return Platforms[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getList()
    {
        return self::all();
    }

    /**
     * 获取cookie状态文本
     * @return string
     */
    public function cookieStatusText()
    {
        if(empty($this->cookie)){
            return '未导入';
        }
        return '已导入';
    }
}
