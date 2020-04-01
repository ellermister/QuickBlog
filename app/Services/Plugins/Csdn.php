<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/2/12
 * Time: 23:23
 */

namespace App\Services\Plugins;


use App\Model\PostsSchemes;
use App\Services\Plugin;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use Exception;
use Parsedown;

class Csdn extends Plugin
{
    protected $name = "csdn";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "CSDN";
    protected $describe = "CSDN-专业IT技术社区";
    protected $img = "csdn-logo.jpg";

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
        return [];
    }

    public function updateScheme(PostsSchemes $postsScheme)
    {
        // 延迟，CSDN频繁发布有警告
        sleep(1);
        // TODO: Implement updateScheme() method.
        if($postsScheme->isWaitSyncStatus()){
            // $uid = $this->getUid();
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try{
                if(empty($postsScheme->third_id)){
                    //首次同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents,$postsScheme->third_id);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
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
     * 更新文章
     * @param $title
     * @param $content
     * @param null $thirdId
     * @return array
     */
    protected function sendPost($title, $content, $thirdId = null)
    {
        $url = "https://blog-console-api.csdn.net/v1/mdeditor/saveArticle";
        $client = new Client();
        $Parsedown = new Parsedown();
        $payload = [
            'authorized_status' => false,
            'categories'        => '',
            'content'           => $Parsedown->text($content),
            'markdowncontent'   => $content,
            'not_auto_saved'    => "1",
            'original_link'     => "",
            'readType'          => config('APP_DEBUG', false) ? 'private' : 'public',//"private",// public
            'source'            => "pc_mdeditor",
            'status'            => "0",
            'tags'              => "",
            'title'             => $title,
            'type'              => "original",

        ];
        if($thirdId){
            $payload['id'] = $thirdId;
        }
        try {
            $response = $client->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'content-type'=> 'application/json',
                    'Cookie' => $this->getCookie(),
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {

            if (preg_match('/<title>([^<]+)<\/title>/is', $e->getResponse()->getBody(), $matches)) {
                $error = "发表博文时同步时遇到错误：" . $matches[1];
            } else {
                $error = "发表博文时同步时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode()."message:".$e->getMessage();
            }
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        if(isset($data['code']) && $data['code'] == 200 && !empty($data['data']['id'])){
            return [
                'id' => $data['data']['id'],
                'url' => $data['data']['url'],
            ];
        }
        throw new Exception("JSON解析错误:".json_last_error_msg());
    }

    /**
     * 获取用户名
     * @return string
     */
    protected function getUid()
    {
        $url = "https://me.csdn.net/api/user/show";
        $client = new Client();
        $response = $client->request('get', $url,[
            'headers' => [
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        if(isset($data['data']) && isset($data['data']['username'])){
            return $data['data']['username'];
        }
        return "";
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
     * 验证COOKIE有效性
     * @param string $cookie
     * @return bool
     */
    public function verifyCookie(string $cookie): bool
    {
        $url = "https://blog-console-api.csdn.net/v1/user/info";
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
        if (is_array($data) && isset($data['code']) && $data['code'] == 200) {
            return true;
        }
        return false;
    }

}