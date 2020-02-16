<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/2/15
 * Time: 2:24
 */

namespace App\Services\Plugins;

use App\Model\PostsSchemes;
use App\Services\Plugin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use Exception;
use Parsedown;

class Zhihu extends Plugin
{
    protected $name = "zhihu";
    protected $version = "1.0";

    /**
     * 获取COOKIE
     * @return mixed
     */
    public function getCookie()
    {
        return ($this->packageInfo->cookie);
    }

    function categoryList()
    {
       return [];
    }

    public function updateScheme(PostsSchemes $postsScheme)
    {
        // TODO: Implement updateScheme() method.
        if($postsScheme->isWaitSyncStatus()){
            // $uid = $this->getUid();
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try{
                $post = $postsScheme->getPost();
                if(empty($postsScheme->third_id)){
                    //首次同步
                    $res = $this->
                    ($post->title, $post->contents);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->chird_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $res = $this->sendPost($post->title, $post->contents, $postsScheme->third_id);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->setSynced(); //设置已经同步完成
                    }
                }
            }catch (Exception $exception){
                $postsScheme->setSyncFailed();// 同步失败，设置状态
                return $exception->getMessage();
            }
        }
        return false;
    }

    /**
     * 创建草稿
     * @param $title
     * @return mixed
     * @throws Exception
     */
    protected function createDrafts($title)
    {
        $url = "https://zhuanlan.zhihu.com/api/articles/drafts";
        $client = new Client();
        try {
            $response = $client->request('POST', $url, [
                'json' => ['delta_time' => 0, 'title' => $title],
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $this->getCookie(),
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "发表博文时同步时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode()." message:".$e->getMessage();
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody()->getContents();
        $data = json_decode($response,  true);
        if(is_array($data)  &&  isset($data['id'])){
            return $data['id'];
        }
        throw new Exception("创建草稿时遇到错误，响应与预期不符。 response: ". $response);
    }

    protected function sendPost($title, $content, $thirdId = null)
    {
        if(!$thirdId){
            //新文章
            $id = $this->createDrafts($title);
        }else{
            $id = $thirdId;
        }

        // 更新草稿
        if($this->updateDrafts($id, $this->toHtml($content), $title)){
            echo "草稿更新成功".PHP_EOL;
        }

        // 发布

    }

    /**
     * 转换MD为HTML
     * @param $markdown
     * @param $id
     * @return mixed
     * @throws Exception
     */
    protected function toHtml($markdown)
    {
        // 需要将外链全部转换为知乎图片，否则会被吞掉。
        $markdown = preg_replace_callback('/\!\[[^\]]*\]\(([^\)]+)\)/is',function ($matches){
            return str_replace($matches[1], $this->pushImagesToZhihu($matches[1]), $matches[0]);
        },$markdown);
        $Parsedown = new Parsedown;
        $html = $Parsedown->text($markdown);

        // 剔除多余换行
        // 知乎有个问题，通过自定义转换后的HTML5文章，会将换行符转换为<p><br></p>，产生很多换行影响文体。
        $html = str_replace("\n", '',$html);
        return $html;
    }

    /**
     * 更新草稿
     * @param $id
     * @param $content
     * @param null $title
     * @return bool
     * @throws Exception
     */
    protected function updateDrafts($id, $content, $title = null)
    {
        $playload = [
            'content'    => $content,
            'delta_time' => 1,
        ];
        if(!empty($title)) $playload['title'] = $title;
        $url = sprintf("https://zhuanlan.zhihu.com/api/articles/%s/draft", $id);
        $client = new Client();
        try {
            $response = $client->request('PATCH', $url, [
                'json'    => $playload,
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $this->getCookie(),
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "更新草稿内容时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode()." message:".$e->getMessage();
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            throw new Exception($exception->getMessage());
        }
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            return true;
        }
        throw new Exception("更新草稿内容失败，HTTP响应状态：" . $statusCode);
    }

    /**
     * 获取草稿内容
     * @param $id
     * @return mixed
     * @throws Exception
     */
    protected function getDraftsContent($id)
    {
        $url = sprintf("https://zhuanlan.zhihu.com/api/articles/%s/draft", $id);
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $this->getCookie(),
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "获取草稿内容时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode()." message:".$e->getMessage();
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            throw new Exception($exception->getMessage());
        }
        if($response->getStatusCode() == 200){
            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);
            return Arr::get($data,'content');
        }
        throw new Exception("获取草稿内容时失败，HTTP响应状态：".$response->getStatusCode());
    }

    /**
     * 同步图片到知乎
     * @param $imgUrl
     * @return string
     */
    protected function pushImagesToZhihu($imgUrl)
    {
        // 获取上传图片ID和KEY  POST https://api.zhihu.com/images
        // image_hash


        // 上传图片 PUT https://zhihu-pics-upload.zhimg.com/v2-2e24eb2eae06b16828f6e83e319f6aaf


        // 更新图片状态 PUT https://api.zhihu.com/images/1211861351078940672/uploading_status

        // 获取图片外链 https://api.zhihu.com/images/1211861351078940672
        return "https://pic3.zhimg.com/80/v2-0215ba294c5f50389f943665d61efe64_hd.jpeg";
    }

}