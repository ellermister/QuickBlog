<?php
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/2/13
 * Time: 12:25
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


class SegmentFault extends Plugin
{
    protected $name = "segmentfault";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "SegmentFault";
    protected $describe = "SegmentFault 思否是中国领先的新一代开发者社区和专业的技术媒体。";
    protected $img = "segmentfault-logo.jpg";

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

    /**
     * 验证COOKIE有效性
     * @param string $cookie
     * @return bool
     */
    public function verifyCookie(string $cookie): bool
    {
        $url = "https://segmentfault.com/user/finance";
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $cookie,
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ],
                'allow_redirects' => false
            ]);
        } catch (ClientException $e) {
            $error = "验证COOKIE有效性时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            return false;
        } catch (BadResponseException $exception) {
            return false;
        }
        if($response->getStatusCode() == 200){
            return true;
        }
        return false;
    }

    public function updateScheme(PostsSchemes $postsScheme)
    {
        // TODO: Implement updateScheme() method.
        if($postsScheme->isWaitSyncStatus()){
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try{
                $post = $postsScheme->getPost();
                $tags = $this->getTags(explode(',', $post->keywords));
                if(empty($postsScheme->third_id)){
                    //首次同步
                    $res = $this->sendPost($post->title, $post->contents, $tags);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $res = $this->sendPost($post->title, $post->contents,$tags, $postsScheme->third_id);
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
     * 新文章/更新文章
     * @param $title
     * @param $content
     * @param $tags
     * @param null $thirdId
     * @return mixed
     * @throws Exception
     */
    protected function sendPost($title, $content, array $tags, $thirdId = null)
    {
        $referer = 'https://segmentfault.com/write';
        if($thirdId){
            $referer = sprintf("https://segmentfault.com/a/%s/edit",$thirdId);
            $token = $this->getToken($referer);
        }else{
            $token = $this->getToken($referer);
        }
        if(empty($token)){
            throw new Exception("同步文章时，token获取失败, url:".$referer);
        }

        $url = "https://segmentfault.com/api/articles/add?_=".$token;
        $data = [
            'type'      => 1,
            'url'       => '',
            'blogId'    => 0,
            'isTiming'  => 0,
            'created'   => '',
            'weibo'     => 0,
            'license'   => 0,
            'tags'      => implode(',', $tags),
            'title'     => $title,
            'text'      => $content,
            'articleId' => '',
            'draftId'   => '',
        ];
        if($thirdId){
            // 更新文章
            $url = sprintf("https://segmentfault.com/api/article/%s/edit?_=%s", $thirdId, $token);
            $data['articleId'] = $thirdId; // 文章ID
        }else{
            // 新文章
            $draftId = $this->saveDraft($data, $token); //需要先保存草稿，才能进行发布。
            if($draftId){
                $data['draftId'] = $draftId; // 存储草稿ID
            }
        }
        $client = new Client();
        try {
            $response = $client->request('POST', $url, [
                'form_params' => $data,
                'headers' => [
                    'Cookie'           => $this->getCookie(),
                    'user-agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                    'referer'          => $referer,
                ]
            ]);
        } catch (ClientException $e) {

            if (preg_match('/<title>([^<]+)<\/title>/is', $e->getResponse()->getBody(), $matches)) {
                $error = "发表博文时同步时遇到错误：" . $matches[1]."message:".$e->getMessage();
            } else {
                var_dump($e->getResponse()->getBody());
                $error = "发表博文时同步时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode()."message:".$e->getMessage();
            }
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        if(is_array($data) && isset($data['status'])){
            if($data['status'] == 0 && isset($data["data"])){
                return $data["data"];// [id => '', url => '']
            }
            if(isset($data['data'][1]))
                throw new Exception("更新文章时遇到错误：".implode(',', array_values($data['data'][1])));
        }
        throw new Exception("更新文章时，响应JSON解析失败:".json_last_error_msg());
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
     * 获取TOKEN
     * 每个页面需要不同的token，所以需要提供具体页面的URL
     * @param $url
     * @return string|null
     */
    protected function getToken($url)
    {
        $client = new Client();
        $response = $client->request('get', $url,[
            'headers' => [
                'Cookie' => $this->getCookie(),
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                'referer' => 'https://segmentfault.com/write?freshman=1'
            ]
        ]);
        $response = $response->getBody();
        $content = $response->getContents();
        $pos = strpos($content,'w.SF.token');
        if(preg_match('#var\s+(\S+)\s+=\s+(.*?)\,\s+(\S+)\s+=\s+([^;]+);#is', substr($content, $pos),$matches)){
            $value = $matches[2];
            // 去除注释
            $value = preg_replace("#/\*.*?\*/#is","", $value);
            $value = preg_replace("#//[^\n]*#is","", $value);

            $value = str_replace(PHP_EOL,"", $value);
            $value = str_replace("+","", $value);
            $value = str_replace("'","", $value);
            $value = preg_replace("/\s/","", $value);


            // echo $value;
            $token = $value;
            if(preg_match_all('/(\d+),(\d+)/is', $matches[4], $range, PREG_SET_ORDER)){
                foreach($range as $item){
                    $token = substr($token, 0, $item[1]).substr($token,$item[2]);
                }
                return strval($token);
            }
        }
        return null;
    }

    /**
     * 保存草稿
     * @param array $data
     * @param $token
     * @return mixed
     * @throws Exception
     */
    protected function saveDraft(array $data, $token)
    {
        $client = new Client();
        try {
            $url = "https://segmentfault.com/api/article/draft/save?_=".$token;
            $response = $client->request('POST', $url, [
                'form_params' => $data,
                'headers' => [
                    'Cookie'           => $this->getCookie(),
                    'user-agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                    'referer'          => 'https://segmentfault.com/write',
                ]
            ]);
        } catch (ClientException $e) {

            if (preg_match('/<title>([^<]+)<\/title>/is', $e->getResponse()->getBody(), $matches)) {
                $error = "发表博文时同步时遇到错误：" . $matches[1]."message:".$e->getMessage();
            } else {
                var_dump($e->getResponse()->getBody());
                $error = "发表博文时同步时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode()."message:".$e->getMessage();
            }
            Log::error($error);
            throw new Exception($error);
        }catch (BadResponseException $exception){
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        if(is_array($data) && isset($data['status']) && $data['status'] == 0 && isset($data["data"])){
            return $data["data"];
        }
        throw new Exception("保存草稿时遇到错误，响应JSON解析失败:".json_last_error_msg());
    }

    /**
     * 获取文章的标签
     * 通过文章原始标签兑换segmentfault的标签
     * @param array $keywordArr
     * @return array
     */
    protected function getTags(array $keywordArr = [])
    {
        $referer = 'https://segmentfault.com/write';
        $token = $this->getToken($referer);
        $url = "https://segmentfault.com/api/techTags?_=".$token;
        $client = new Client();
        $response = $client->request('get', $url, [
            'headers' => [
                'Cookie'           => $this->getCookie(),
                'user-agent'       => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                'referer'          => $referer,
            ]
        ]);
        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true);
        $tags = [];
        if(is_array($data) && isset($data['data'])){
            foreach($data['data'] as $category){
                foreach($category as $tag){
                    foreach($keywordArr as $currentKeyword){
                        if(strtolower($tag['name']) == strtolower($currentKeyword)){
                            $tags[] =$tag['id'];
                        }
                    }

                }
            }
        }
        return $tags;
    }
}