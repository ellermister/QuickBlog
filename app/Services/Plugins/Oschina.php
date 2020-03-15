<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/1/9
 * Time: 22:57
 */

namespace App\Services\Plugins;

use App\Model\Post;
use App\Services\Plugin;
use App\Model\PostsSchemes;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Exception;

class Oschina extends Plugin
{

    protected $name = "oschina";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "开源中国";
    protected $describe = "OSCHINA.NET 是目前领先的中文开源技术社区。我们传播开源的理念，推广开源项目，为 IT 开...";
    protected $img = "oschina-logo.jpg";

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

    /**
     * 获取COOKIE
     * @return mixed
     */
    public function getCookie()
    {
        return ($this->packageInfo->cookie);
    }

    /**
     * 获取编辑器ID
     * @param $type
     * @return int
     */
    public function editorType($type)
    {
        if(strtolower($type) == 'markdown'){
            return 3;
        }
        if(strtolower($type) == 'ckeditor'){
            return 4;
        }
        return 4;
    }

    /**
     * 获取用户UID
     * @param string|null $cookie
     * @return string
     */
    public function getUid(string $cookie = null)
    {
        $url = "https://my.oschina.net/";
        $client = new Client();
        $response = $client->request('get', $url,[
            'headers' => [
                'Cookie' => $cookie ? $cookie : $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ],
            'allow_redirects' => false
        ]);
        $location = $response->getHeaderLine('location');
        if(preg_match('/\/u\/(\d+)/i', $location, $matches)){
            return $matches[1];
        }
        return "";
    }

    /**
     * 获取用户CODE
     * @return string
     */
    public function getUserCode()
    {
        $url = "https://my.oschina.net/";
        $client = new Client();
        $response = $client->request('get', $url,[
            'headers' => [
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $response = $response->getBody();
        if(preg_match('/data-name="g_user_code".*?data-value="([^"]+)"/is', $response->getContents(), $matches)){
            return $matches[1];
        }
        return "";
    }

    /**
     * 发送文章请求
     * @param $title
     * @param $content
     * @param $unionCategory
     * @param null $id 留空则新建
     * @return mixed
     * @throws Exception
     */
    public function sendPost($title, $content, array $unionCategory, $id = null)
    {
        $uid = $this->getUid();
        $url = "https://my.oschina.net/u/{$uid}/blog/save";
        if($id){
            $url = "https://my.oschina.net/u/{$uid}/blog/edit";
        }

        // 分类
        $cat_id = 0;
        if(!empty($unionCategory)){
            $cat_id = $unionCategory['id'] ?? 0;//6634180 默认分类
        }

        $client = new Client();
        try {
            $response = $client->request('POST', $url, [
                'form_params' => [
                    'draft'          => '',
                    'id'             => $id ?? '',
                    'user_code'      => $this->getUserCode(),
                    'title'          => $title,
                    'content'        => $content,
                    'content_type'   => $this->editorType('markdown'),
                    'catalog'        => $cat_id,
                    'classification' => '428640',
                    'type'           => 1,
                    'origin_url'     => '',
                    'privacy'        => config('APP_DEBUG', false) ? 1 : 0,
                    'deny_comment'   => 0,
                    'as_top'         => 0,
                    'downloadImg'    => 0,
                    'isRecommend'    => 0
                ],
                'headers' => [
                    'Cookie' => $this->getCookie(),
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {

            if (preg_match('/<title>([^<]+)<\/title>/is', $e->getResponse()->getBody(), $matches)) {
                $error = "发表博文时同步时遇到错误：" . $matches[1];
            } else {
                $error = "发表博文时同步时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode();
            }
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            // 需要判断文章是否存在
//            if ($exception->hasResponse()) {
//                echo $url.PHP_EOL;
//                var_dump($exception->getResponse()->getBody()->getContents());exit;
//            }
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody();
//        $response = '{"code":1,"message":"发表成功","result":{"abstracts":"sefsd","as_top":0,"catalog":6634180,"classification":428640,"content":"sefsd","content_type":4,"daily_blog":0,"donate_count":0,"extid":0,"folder":0,"id":3155597,"options":9,"origin_url":"","project":0,"recomm":0,"reply_count":0,"space":2366984,"title":"国庆节快乐","type":1,"view_count":0,"vote_count":0,"word_count":7},"time":"2020-01-10 01:39:18"}';
        $json = json_decode($response);
        if(!$json){
            Log::error("响应值JSON错误：".json_last_error_msg(). $response);
            throw new Exception("响应值JSON错误：".json_last_error_msg());
        }
        if($json->code == 1){
            return $json;
        }
        Log::error("正确响应，错误内容：".$json->message ?? '');
        throw new Exception("正确响应，错误内容：".$json->message ?? '');
    }

    /**
     * 更新文章计划
     * @param PostsSchemes $postsScheme
     * @return bool|string
     */
    public function updateScheme(PostsSchemes $postsScheme)
    {
        $uid = $this->getUid();
        if($postsScheme->isWaitSyncStatus()){
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try{
                if(empty($postsScheme->third_id)){
                    //首次同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents, $postsScheme->getUnionCategory());
                    if($res){
                        $postsScheme->third_id = $res->result->id;
                        $postsScheme->third_url = sprintf("https://my.oschina.net/u/%s/blog/%s", $uid, $res->result->id);
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents, $postsScheme->getUnionCategory(), $postsScheme->third_id);
                    if($res){
                        $postsScheme->third_id = $res->result->id;
                        $postsScheme->third_url = sprintf("https://my.oschina.net/u/%s/blog/%s", $uid, $res->result->id);
                        $postsScheme->setSynced(); //设置已经同步完成
                    }
                }
            }catch (Exception $exception){
                $postsScheme->setSyncFailed();// 同步失败，设置状态
                return $exception->getMessage();
            }


        }else{
            // 其他状态目前不执行
        }
        return false;
    }

    /**
     * 分类列表
     * 作为平台设置页展示关联使用
     * @return array
     */
    public function categoryList(): array
    {
        $list = [];
        $uid = $this->getUid();
        $url = sprintf("https://my.oschina.net/u/%s/blog/write", $uid);
        $client = new Client();
        $response = $client->request('get', $url,[
            'headers' => [
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $response = $response->getBody();
        if(preg_match('/<select\s+name="catalog"[^>]+>(.*?)<\/select>/is', $response->getContents(), $matches)){
            if(preg_match_all('/<option\s+value="([^"]+)">([^<]+)<\/option>/is', $matches[1], $matches2,PREG_SET_ORDER)){
                foreach($matches2 as $item){
                    $list[$item[1]] = $item[2];
                }
            }
        }
        return $list;
    }

    /**
     * 验证COOKIE有效性
     * @param string $cookie
     * @return bool
     */
    public function verifyCookie(string $cookie): bool
    {
        $uid = $this->getUid($cookie);
        return empty($uid) ? false : true;
    }

}