<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/1/9
 * Time: 23:50
 */

namespace App\Services;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Foundation\Application;

class PluginManager
{
    /**
     * @var Application
     */
    protected $app;


    /**
     * @var Collection|null
     */
    protected $plugins;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @throws \ReflectionException
     */
    public function getPlugins()
    {
        if (is_null($this->plugins)){
            $plugins = new Collection();
            $pluginDir = __DIR__.DIRECTORY_SEPARATOR.'Plugins';
            $resource = opendir($pluginDir);

            // traverse plugins dir
            while($filename = @readdir($resource)) {
                if ($filename == "." || $filename == "..")
                    continue;
                $path = $pluginDir."/".$filename;
                if (is_file($path)) {
                    $installed[] = $path;
                }
            }
            closedir($resource);

            foreach ($installed as $path) {
                $class = basename($path,".php");
                $reflectionClass  = new \ReflectionClass("\\App\\Services\\Plugins\\".$class);
                $plugin = $reflectionClass->newInstance();
                $plugins->put($plugin->name, $plugin);
            }

            $this->plugins = $plugins->sortBy(function ($plugin, $name) {
                return $plugin->name;
            });
        }
        return $this->plugins;
    }
}