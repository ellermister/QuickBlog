<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2019/10/27
 * Time: 14:19
 */

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class PluginManager
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var Filesystem
     */
    protected $filesystem;


    /**
     * @var Collection|null
     */
    protected $plugins;

    public function __construct(
        Application $app,
        Dispatcher $dispatcher,
        Filesystem $filesystem
    ) {
        $this->app        = $app;
        $this->dispatcher = $dispatcher;
        $this->filesystem = $filesystem;
    }

    public function getPlugins()
    {
        if (is_null($this->plugins)) {
            $plugins = new Collection();
            $resource = opendir(base_path('plugins'));

            // traverse plugins dir
            while($filename = @readdir($resource)) {
                if ($filename == "." || $filename == "..")
                    continue;
                $path = base_path('plugins')."/".$filename;
                if (is_dir($path)) {
                    if (file_exists($path."/package.json")) {
                        // load packages installed
                        $installed[$filename] = json_decode($this->filesystem->get($path."/package.json"), true);
                    }
                }
            }
            closedir($resource);

            foreach ($installed as $path => $package) {
                // Instantiates an Plugin object using the package path and package.json file.
                $plugin = new Plugin($this->getPluginsDir().'/'.$path, $package);
                // Per default all plugins are installed if they are registered in composer.
                $plugin->setNameSpace(Arr::get($package, 'namespace'));
                $plugin->setEnabled($this->isEnabled($plugin->name));
                $plugins->put($plugin->name, $plugin);
            }

            $this->plugins = $plugins->sortBy(function ($plugin, $name) {
                return $plugin->name;
            });
        }

        return $this->plugins;
    }

    /**
     * Get only enabled plugins.
     *
     * @return Collection
     */
    public function getEnabledPlugins()
    {
        return $this->getPlugins()->only($this->getEnabled());
    }

    /**
     * Loads all bootstrap.php files of the enabled plugins.
     *
     * @return Collection
     */
    public function getEnabledBootstrappers()
    {
        $bootstrappers = new Collection;
        foreach ($this->getEnabledPlugins() as $plugin) {
            if ($this->filesystem->exists($file = $plugin->getPath().'/bootstrap.php')) {
                $bootstrappers->push($file);
            }
        }
        return $bootstrappers;
    }


    /**
     * The id's of the enabled plugins.
     *
     * @return array
     */
    public function getEnabled()
    {
        return (array) json_decode($this->option->get('plugins_enabled'), true);
    }
    /**
     * Persist the currently enabled plugins.
     *
     * @param array $enabled
     */
    protected function setEnabled(array $enabled)
    {
        $enabled = array_values(array_unique($enabled));
        $this->option->set('plugins_enabled', json_encode($enabled));
    }


    /**
     * Whether the plugin is enabled.
     *
     * @param $plugin
     * @return bool
     */
    public function isEnabled($plugin)
    {
        return in_array($plugin, $this->getEnabled());
    }

    /**
     * 获取插件目录
     *
     * @return string
     */
    protected function getPluginsDir()
    {
        return $this->app->basePath().'/plugins';
    }

}