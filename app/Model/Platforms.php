<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Platforms extends Model
{
    protected $dateFormat = 'U';

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
        if (empty($this->cookie)) {
            return '未导入';
        }
        return '已导入';
    }

    /**
     * 设置账户信息
     * @param array $data
     * @return bool
     */
    public function setAccount(array $data)
    {
        $cookie = $data['cookie'] ?? '';

        $this->account = json_encode($data);
        $this->cookie = $cookie;
        return $this->save();
    }

    /**
     * 获取账户字段数据
     * @param string $name
     * @param string $default
     * @return mixed|string
     */
    public function account(string $name, $default = '')
    {
        try {
            $data = json_decode($this->account, true);
        } catch (\Exception $exception) {
            $data = [];
        }
        return $data[$name] ?? $default;
    }

    /**
     * 获取插件配置信息
     * @param $name
     * @return array
     */
    public static function getPlatformInfo($name)
    {
        return self::where('name', $name)->first();
    }

    /**
     * 获取分类列表
     * @return array
     */
    public function getCategoryList()
    {
        $pluginManager = app(\App\Services\PluginManager::class);
        foreach($pluginManager->getPlugins() as $plugin){
            if($plugin->name == $this->name){
                return $plugin->categoryList();
            }
        }
        return [];
    }
}
