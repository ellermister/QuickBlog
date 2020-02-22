<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PluginInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plugin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '插件安装';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $pluginManager = app(\App\Services\PluginManager::class);
        foreach($pluginManager->getPlugins() as $plugin){
            if($plugin->installed() === false){
                try{
                    $plugin->install();
                }catch (\Exception $exception){
                    $this->error(sprintf('插件安装出错[%s]:'.$exception->getMessage(), $plugin->getName()));
                }
            }else{
                try{
                    $plugin->updateInstall();
                }catch (\Exception $exception){
                    $this->error(sprintf('插件更新出错[%s]:'.$exception->getMessage(), $plugin->getName()));
                }

            }
        }
    }
}
