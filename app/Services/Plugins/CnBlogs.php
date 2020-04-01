<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/2/22
 * Time: 23:53
 */

namespace App\Services\Plugins;


use App\Model\PostsSchemes;
use App\Services\Plugin;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class CnBlogs extends Plugin
{
    protected $name = "cnblogs";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "博客园";
    protected $describe = "博客园是一个面向开发者的知识分享社区。程序员问答社区，解决程序员的技术难题。";
    protected $img = "cnblogs-logo.gif";

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
     * 验证cookie有效性
     * @param string $cookie
     * @return bool
     */
    public function verifyCookie(string $cookie): bool
    {
        $user = $this->getUserInfo($cookie);
        if(isset($user['blogId'])){
            return true;
        }
        return false;
    }

    /**
     * 获取用户信息
     * @param null $cookie
     * @return bool|mixed
     */
    protected function getUserInfo($cookie = null)
    {
        if($cookie == null){
            $cookie = $this->getCookie();
        }
        $url = "https://i-beta.cnblogs.com/api/user";
        $client = new Client();
        $response = $client->request('get', $url, [
            'headers' => [
                'content-type' => 'application/json',
                'Cookie'       => $cookie,
                'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        if (isset($data['blogId'])) {
            return $data;
        }
        return false;
    }

    /**
     * 获取博客ID
     * @return int
     */
    protected function getBlogId()
    {
        $user = $this->getUserInfo();
        if(isset($user['blogId'])){
            return $user['blogId'];
        }
        return 0;
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
     * 分类列表
     * @return array
     */
    function categoryList(): array
    {
        $url = "https://i-beta.cnblogs.com/api/category/blog/1/edit";
        $client = new Client();
        $response = $client->request('get', $url, [
            'headers' => [
                'content-type' => 'application/json',
                'Cookie'       => $this->getCookie(),
                'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        $category = [];
        if(is_array($data)){
            foreach($data as $item){
                $category[$item['categoryId']] = $item['title'];
            }
        }
        return $category;
    }

    /**
     * 更新同步计划
     * @param PostsSchemes $postsScheme
     * @return bool|string
     */
    public function updateScheme(PostsSchemes $postsScheme)
    {
        if($lastUpdate = Cache::get('cnblogs_lastupdate')){
            if(time() - intval($lastUpdate) <= 60){
                return "cnblogs一分钟只能发送一篇文章，其他任务将到下次推送";
            }
        }
        if($postsScheme->isWaitSyncStatus()){
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try{
                if(empty($postsScheme->third_id)){
                    //首次同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents,$post->description,$post->keywords, $postsScheme->getUnionCategory());
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents, $post->description,$post->keywords, $postsScheme->getUnionCategory(), $postsScheme->third_id);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
                        $postsScheme->setSynced(); //设置已经同步完成
                        Cache::put('cnblogs_lastupdate',time(),60);
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
     * 博客园的id是强int32型，如果字段值为string类型的数值，实际会报错。
     *
     * @param $title
     * @param $content
     * @param $describe
     * @param $keywords
     * @param $unionCategory
     * @param null $thirdId
     * @return array
     * @throws Exception
     */
    protected function sendPost($title, $content, $describe, $keywords,$unionCategory, $thirdId = null)
    {
        $url = "https://i-beta.cnblogs.com/api/posts";
        $client = new Client();
        $payload = [
            'author'                   => null,
            'autoDesc'                 => null,
            'blogId'                   => 0,
            'blogTeamIds'              => null,
            'canChangeCreatedTime'     => false,
            'categoryIds'              => [intval($unionCategory['id'])], // 这里可以多个值的，不过只关联一个
            'changeCreatedTime'        => false,
            'changePostType'           => false,
            'datePublished'            => gmdate("Y-m-d\TH:i:s\Z"),
            'description'              => $describe,
            'displayOnHomePage'        => true,
            'entryName'                => null,
            'id'                       => null,
            'inSiteCandidate'          => false,
            'inSiteHome'               => false,
            'includeInMainSyndication' => true,
            'ip'                       => null,
            'isAllowComments'          => true,
            'isDraft'                  => true,
            'isMarkdown'               => true,
            'isOnlyForRegisterUser'    => false,
            'isPinned'                 => false,
            'isPublished'              => true,
            'isUpdateDateAdded'        => false,
            'password'                 => null,
            'postBody'                 => $content,
            'postType'                 => 1,
            'removeScript'             => false,
            'siteCategoryId'           => null,
            'tags'                     => explode(',',$keywords),
            'title'                    => $title,
            'url'                      => '',

        ];
        if($thirdId){
            $payload['id'] = intval($thirdId);
        }
        try {
            $response = $client->request('POST', $url, [
                'json' => $payload,
                'headers' => [
                    'content-type'=> 'application/json',
                    'Cookie' => $this->getCookie(),
                    'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                    'x-blog-id' => $this->getBlogId()
                ]
            ]);
        } catch (ClientException $e) {
            $exceptionJson = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(),true);
            if (isset($exceptionJson['errors'])) {
                $error = "发表博文时同步时遇到错误：" . implode('',$exceptionJson['errors']);
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
        if (isset($data['id']) && !empty($data['id'])) {
            return [
                'id'  => $data['id'],
                'url' => $data['url'],
            ];
        }
        throw new Exception("JSON解析错误:".json_last_error_msg());
    }

}