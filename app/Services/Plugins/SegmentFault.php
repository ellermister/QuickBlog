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

    function categoryList()
    {
        return [];
    }

    public function updateScheme(PostsSchemes $postsScheme)
    {
        // TODO: Implement updateScheme() method.
        if($postsScheme->isWaitSyncStatus()){
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try{
                if(empty($postsScheme->third_id)){
                    //首次同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->chird_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                }else{
                    // 更新同步
                    $post = $postsScheme->getPost();
                    $res = $this->sendPost($post->title, $post->contents,$postsScheme->third_id);
                    if($res){
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->chird_url = $res['url'];
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

    protected function sendPost($title, $content, $thirdId = null)
    {
        $referer = 'https://segmentfault.com/write';
        if($thirdId){
            $referer = sprintf("https://segmentfault.com/a/%s/edit",$thirdId);
            $token = $this->getToken($referer);
        }else{
            $token = $this->getToken($referer);
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
            'tags'      => '1040000000089436,1040000000089899',//标签，待更新
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
        if(is_array($data) && isset($data['status']) && $data['status'] == 0 && isset($data["data"])){
            return $data["data"];// [id => '', url => '']
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
            // 需要判断文章是否存在
//            if ($exception->hasResponse()) {
//                echo $url.PHP_EOL;
//                var_dump($exception->getResponse()->getBody()->getContents());exit;
//            }
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody();
        $data = json_decode($response->getContents(), true);
        if(is_array($data) && isset($data['status']) && $data['status'] == 0 && isset($data["data"])){
            return $data["data"];
        }
        throw new Exception("保存草稿时遇到错误，响应JSON解析失败:".json_last_error_msg());
    }
}