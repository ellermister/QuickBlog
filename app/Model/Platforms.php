<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Platforms extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = ["name", "title", "describe", "img"];

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
     * 是否安装插件
     * @param $name
     * @return bool
     */
    public static function hasInstalled($name)
    {
        return self::where('name', $name)->count() ? true : false;
    }

    /**
     * 插件安装
     * @param array $info
     * @return bool
     * @throws \Exception
     */
    public static function installPlugin(array $info)
    {
        $data = [
            'name'     => Arr::get($info, "name", ''),
            'title'    => Arr::get($info, "title", ''),
            'describe' => Arr::get($info, "describe", ''),
            'img'      => Arr::get($info, "img", ''),
        ];

        // 检查两个最重要的参数
        if(!isset($data['name']) || !isset($data['title'])){
            throw new \Exception("插件安装参数不全");
        }

        $platform = self::create($data);
        return $platform ? true : false;
    }

    /**
     * 更新安装
     * @param array $info
     * @return bool
     * @throws \Exception
     */
    public static function updateInstall(array $info)
    {
        $name = Arr::get($info, 'name', '');
        if (empty($name)) {
            throw new \Exception("插件名称不能为空");
        }
        $data = Arr::only($info, ['title', 'describe', 'img']);
        return self::where('name', $name)->update($data) ? true : false;
    }

    /**
     * 获取分类列表
     * @return array
     */
    public function getCategoryList()
    {
        try{
            $pluginManager = app(\App\Services\PluginManager::class);
            foreach ($pluginManager->getPlugins() as $plugin) {
                if ($plugin->name == $this->name) {
                    return $plugin->categoryList();
                }
            }
        }catch (\Exception $exception){
            return [];
        }

        return [];
    }

    /**
     * 验证并设置COOKIE
     * @param $cookie
     * @return bool
     */
    public function verifyAndSetCookie($cookie)
    {
        $pluginManager = app(\App\Services\PluginManager::class);
        foreach ($pluginManager->getPlugins() as $plugin) {
            if ($plugin->name == $this->name) {
                if(boolval($plugin->verifyCookie($cookie))){
                    $this->cookie = $cookie;
                    $this->save();
                    return true;
                }
                return false;
            }
        }
        return  false;
    }

}
