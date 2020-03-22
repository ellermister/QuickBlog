<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Setting extends Model
{
    protected $dateFormat = 'U';
    public $fillable = ['site_name','site_keyword','site_describe'];
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * 获取键值对配置
     * @return array
     */
    public static function getSetting()
    {
        $list = self::all();
        $data = [];
        foreach($list as $item){
            $data[$item->name] = $item->value;
        }
        return collect($data);
    }
}
