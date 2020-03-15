<?php

namespace App\Console\Commands;

use App\Model\PostsSchemes;
use App\Services\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PostSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步博文';

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
     * 同步博文
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $this->info('> 开始同步计划');
        $pluginManager = app(\App\Services\PluginManager::class);
        foreach($pluginManager->getPlugins() as $plugin){
            $postScheme = PostsSchemes::getSchemes($plugin->getName());
            foreach ($postScheme as $scheme){
                $this->info('同步计划 post_id:'.$scheme->post_id);
                $error = $plugin->updateScheme($scheme);

                $shortTitle = mb_substr($scheme->getPost()->title, 0, 15);
                if(is_string($error)){
                    $formatError = sprintf("计划ID:%s 文章标题:%s 错误：%s", $scheme->id, $shortTitle, $error);
                    Log::channel('schemes')->error($formatError);
                    $this->error($error);
                }else{
                    $message = sprintf("计划ID:%s 文章标题:%s 同步完成", $scheme->id, $shortTitle);
                    Log::channel('schemes')->info($message);
                }
            }
        }
        $this->info('> 结束同步计划');
    }
}
