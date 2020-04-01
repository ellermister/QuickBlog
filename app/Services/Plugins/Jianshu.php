<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/2/14
 * Time: 13:44
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


class Jianshu extends Plugin
{
    protected $name = "jianshu";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "简书";
    protected $describe = "简书是一个优质的创作社区，在这里，你可以任性地创作，一篇短文、一张照片、一首诗、一幅画…";
    protected $img = "jianshu-logo.png";

    /**
     * 插件安装时信息
     * @return array
     */
    function installInfo()
    {
        return [
            'author'   => $this->author,
            'name'     => $this->name,
            'title'    => $this->title,
            'describe' => $this->describe,
            'img'      => $this->img
        ];
    }

    function categoryList(): array
    {
        $url = "https://www.jianshu.com/author/notebooks";
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Cookie'     => $this->getCookie(),
                    'Accept'     => 'application/json',
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            if($e->getCode() == 401){
                // COOKIE失效
            }
            $error = "获取分类列表时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            throw new Exception($error);
        } catch (BadResponseException $exception) {
            throw new Exception($exception->getMessage());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        $list = [];
        if(is_array($data)){
            foreach ($data as $item){
                $list[$item['id']] = $item['name'];
            }
        }
        return $list;
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
                    $res = $this->sendPost($post->title, $post->contents, $postsScheme->getUnionCategory());
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $res = $this->sendPost($post->title, $post->contents, $postsScheme->getUnionCategory(), $postsScheme->third_id);
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

    protected function sendPost($title, $content, $category, $thirdId = null)
    {
        if(!$thirdId){
            //新文章
            $res = $this->createNotes($title, $category);
            $id = $res['id'];
            $slug = $res['slug'];
        }else{
            $id = $thirdId;
        }

        if(empty($category)){
            $categoryList = $this->categoryList();
            $categoryId = Arr::first(array_keys($categoryList));
        }else{
            $categoryId = Arr::get($category,'id','0');
        }

        $payload = [
            'autosave_control' => $this->getAutoSaveId($id, $categoryId) + 1,
            'content'          => $content,
            'title'            => $title,
            'id'               => $id,
        ];
        $url = "https://www.jianshu.com/author/notes/".$id;
        $client = new Client();
        try {
            $response = $client->request('PUT', $url, [
                'json' => $payload,
                'headers' => [
                    'content-type'=> 'application/json',
                    'Accept'=> 'application/json',
                    'Cookie' => $this->getCookie(),
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
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
        if(is_array($data) && isset($data['content_size_status']) &&  isset($data['id'])){
            sleep(5);// 简书发的太快会被检测
            if($this->publicize($data['id'])){
                return ['id' => $data['id'],'url' => sprintf('https://www.jianshu.com/p/%s', $slug ?? '')];// 更新时这个值为空
            }
        }
        throw new Exception("同步文章失败，响应解析失败:", $response);
    }

    /**
     * 创建笔记
     * @param $title
     * @param $noteBookId
     * @param $url
     * @return mixed
     * @throws Exception
     */
    protected function createNotes($title, $noteBookId)
    {
        if(empty($noteBookId)){
            // 获取默认分类ID
            $noteBookId = "42826786";
            $categoryList = $this->categoryList();
            if(empty($categoryList)){
                throw new Exception("更新文章，分类未指定，获取默认分类失败。");
            }
            $noteBookId = Arr::first(array_keys($categoryList));
        }
        $client = new Client();
        $url = "https://www.jianshu.com/author/notes";
        $payload = [
            'at_bottom' => true,
            'notebook_id' => $noteBookId,// 分类ID 42826786
            'title' => $title,
        ];
        $response = $client->request('POST', $url, [
            'json' => $payload,
            'headers' => [
                'content-type'=> 'application/json',
                'Accept'=> 'application/json',
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        if(is_array($data)){
            if(isset($data['id'])){
                return $data;// [slug => '', id => '']
            }
        }
        throw new Exception("同步文章时遇到错误:JSON响应预期不符, 如下：".json_encode($data));
    }

    protected function publicize($id)
    {
        $url = sprintf("https://www.jianshu.com/author/notes/%s/publicize", $id);
        $client = new Client();
        $payload = [];
        $response = $client->request('POST', $url, [
            'json' => $payload,
            'headers' => [
                'content-type'=> 'application/json',
                'Accept'=> 'application/json',
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        if(is_array($data)){
            if(isset($data['last_compiled_at'])){
                return true;
            }
        }
        throw new Exception("同步文章时遇到错误:JSON响应预期不符, 如下：".json_encode($data));
    }

    /**
     * 获取COOKIE
     * @return mixed
     */
    public function getCookie()
    {
        return ($this->packageInfo->cookie);
    }

    /**
     * 获取自动保存自增ID
     * @param $id
     * @param $categoryId
     * @return int
     */
    protected function getAutoSaveId($id, $categoryId)
    {
        $url = sprintf("https://www.jianshu.com/author/notebooks/%s/notes", $categoryId);
        $client = new Client();
        $payload = [];
        $response = $client->request('GET', $url, [
            'json' => $payload,
            'headers' => [
                'content-type'=> 'application/json',
                'Accept'=> 'application/json',
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        if(is_array($data)){
            foreach($data as $item){
                if($item['id'] == $id){
                    return $item['autosave_control'];
                }
            }
        }
        // 没找到具体文章，也可以从其他分类中继续尝试获取。
        return 0;
    }

    /**
     * 验证COOKIE有效性
     * @param string $cookie
     * @return bool
     */
    public function verifyCookie(string $cookie): bool
    {
        $url = "https://www.jianshu.com/settings/basic.json";
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $cookie,
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "验证COOKIE有效性时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            return false;
        } catch (BadResponseException $exception) {
            return false;
        }
        $response = $response->getBody()->getContents();
        $data = json_decode($response, true);
        if (is_array($data) && isset($data['data'])) {
            return true;
        }
        return false;
    }

}