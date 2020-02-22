<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/1/9
 * Time: 22:49
 */

namespace App\Services;
use App\Model\Platforms;
use App\Model\PostsSchemes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Contracts\Support\Arrayable;

abstract class Plugin implements Arrayable
{

    protected $name;
    protected $version;
    protected $packageInfo;

    function __construct()
    {
        $this->packageInfo = Platforms::getPlatformInfo($this->name) ?? Collection::make([]);
    }

    public function __get($name)
    {
        return $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    public function __isset($name)
    {
        return isset($this->{$name}) || $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    public function packageInfoAttribute($name)
    {
        return Arr::get($this->packageInfo->toArray(), $name);
    }

    /**
     * Generates an array result for the object.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) array_merge([
            'name'          => $this->name,
            'version'       => $this->getVersion(),
        ], $this->packageInfo);
    }

    public function getVersion()
    {
        $this->version ?? '';
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 是否安装插件
     * @return bool
     */
    public function installed()
    {
        return Platforms::hasInstalled($this->name);
    }

    /**
     * 安装插件
     * @throws \Exception
     */
    public function install()
    {
        if($this->installed() === false){
            Platforms::installPlugin($this->installInfo());
        }
    }

    /**
     * 更新插件
     * @throws \Exception
     */
    public function updateInstall()
    {
        if($this->installed() === true){
            Platforms::updateInstall($this->installInfo());
        }
    }

    /**
     * 必须返回 ['author' => '', 'name' => '', 'title' => '', 'describe' => '', 'img' => '']
     * @return array
     */
    abstract function installInfo();

    /**
     * 分类列表接口
     * 必须返回键值对 [ '分类ID' => '分类名]
     * @return array
     */
    abstract function categoryList();

    /**
     * 更新同步计划
     * 插件需要继承并实现
     * @param PostsSchemes $postsScheme
     * @return bool|string
     */
    abstract public function updateScheme(PostsSchemes $postsScheme);
    // 同步过程，需要自行try catch捕获，防止发生错误。

    // 首先判断是否是需要同步的计划
    // if( $postsScheme->isWaitSyncStatus() ) {}

    // 开始同步时需要将计划设置为正在同步，防止重复同步。
    // $postsScheme->setSynching();

    // 同步成功，需要将计划设置为成功。
    // $postsScheme->setSynced(); //设置已经同步完成

    // 如果同步失败，则需要将计划设置为失败状态。
    // $postsScheme->setSyncFailed();// 同步失败，设置状态

    // 如果同步出错，需要返回错误字符串，将会在命令行展示。
    // return $exception->getMessage();

}