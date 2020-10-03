<?php

namespace App\Console\Commands;

use App\Model\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;

class PostCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:collection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '博文采集: 同步采集表到发布表';

    /**
     * 来源表名
     * @var string
     */
    protected $fromTable = "";

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
        $this->fromTable = env('POST_COLLECTION_TABLE');
        if(!$this->fromTable){
            $this->error('未设置环境变量表名：POST_COLLECTION_TABLE');
            return false;
        }
        DB::table($this->fromTable)->chunkById(10,function($posts){
            foreach ($posts as $post){
                try{
                    $result = DB::transaction(function() use($post){
                        $catId = 0;
                        if($post->post_id == 0){
                            $body_html = $this->replaceImg($post->body_html);
                            $converter = new HtmlConverter();
                            $markdown = $converter->convert($body_html);
                            $newPost = Post::create([
                                'title'       => $post->title,
                                'keywords'    => $post->keywords,
                                'description' => $post->describe,
                                'post_img'    => $this->getThumbnail($markdown),
                                'contents'    => $markdown,
                                'category'    => '',
                                'cat_id'      => $catId,
                                'is_show'     => 1,
                                'is_sync'     => 0,
                                'is_original' => 0,
                            ]);
                            return DB::table($this->fromTable)->where('id', $post->id)->update(['post_id' => $newPost->id]);
                        }else{
                            $body_html = $this->replaceImg($post->body_html);
                            $converter = new HtmlConverter();
                            $markdown = $converter->convert($body_html);
                            return Post::where('id', $post->post_id)->update([
                                'title'       => $post->title,
                                'keywords'    => $post->keywords,
                                'description' => $post->describe,
                                'post_img'    => $this->getThumbnail($markdown),
                                'contents'    => $markdown,
                                'category'    => '',
                                'cat_id'      => $catId,
                                'is_original' => 0,
                            ]);
                        }
                    });
                }catch (\Exception $exception){
                    if($exception->getCode() == 1062){
                        $this->error("文章重复:".$post->title);
                    }else{
                        $this->error("文章出现错误:".$exception->getMessage());
                    }
                }
                if($result){
                    $this->info("成功创建一篇文章: ".$post->title);
                }else{
                    $this->error("失败一篇文章: ".$post->title);
                }
            }
        });


    }

    protected function replaceImg($html)
    {
        return preg_replace_callback('/<img.*?src="([^"]+)"[^>]+>/is', function ($matches) {
            return str_replace($matches[1], $this->convertImgToLocal($matches[1]), $matches[0]);
        }, $html);
    }


    /**
     * 将远程的图片离线存储到本地磁盘
     *
     * @param $url
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function convertImgToLocal($url)
    {
        if(preg_match('#^https?://#', $url)){
            $image = $this->getImagesToLocal($url);
            return Storage::url($image['path']);
        }else if(preg_match('#^//#')){
            $image = $this->getImagesToLocal($url);
            return Storage::url($image['path']);
        }else{
            return $url;
        }
    }

    /**
     * 将图片缓存到本地
     * @param $url
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function getImagesToLocal($url)
    {
        $urlInfo = parse_url($url);
        $pathInfo = pathinfo($urlInfo['path']);
        $ext = $pathInfo['extension'] ?? 'jpg';
        $localPath = 'public/images_other/' . md5($url) . ".{$ext}";
        if(Storage::disk('local')->exists($localPath)){
            $img = Storage::disk('local')->get($localPath);
            return ['path' => 'public/images_other/' . md5($url) . ".{$ext}", 'hash' => md5($img)];
        }
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];
        $img = file_get_contents($url,
            false, stream_context_create($stream_opts));
        Storage::disk('local')->put($localPath, $img);
        return ['path' => 'public/images_other/' . md5($url) . ".{$ext}", 'hash' => md5($img)];
    }

    /**
     * 获取缩略图
     */
    public function getThumbnail($contents)
    {
        if (preg_match('/\!\[[^\]]*\]\(([^\)]+)\)/is', $contents, $result)) {
            if (isset($result[1])) {
                if (preg_match('/([\S]+)\s/is', $result[1], $img)) {
                    return $img[1];
                }
                return $result[1];
            }
        }
        return '';
    }
}
