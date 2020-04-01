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
use Illuminate\Support\Facades\Storage;
use Parsedown;
use OSS\OSSClient;
use OSS\Core\OSSException;
use SebastianBergmann\CodeCoverage\Report\PHP;

class Zhihu extends Plugin
{
    protected $name = "zhihu";
    protected $version = "1.0";
    protected $author = "ellermister";
    protected $title = "知乎";
    protected $describe = "有问题，上知乎。知乎，可信赖的问答社区，以让每个人高效获得可信赖的解答为使命。";
    protected $img = "zhihu-logo.jpg";

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

    function categoryList(): array
    {
        return [];
    }

    /**
     * 验证COOKIE有效性
     * @param string $cookie
     * @return bool
     * @throws Exception
     */
    public function verifyCookie(string $cookie): bool
    {
        $url = "https://www.zhihu.com/api/v4/answer_later/count";
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
        if (is_array($data) && isset($data['count'])) {
            return true;
        }
        return false;
    }


    public function updateScheme(PostsSchemes $postsScheme)
    {
        // TODO: Implement updateScheme() method.
        if ($postsScheme->isWaitSyncStatus()) {
            // $uid = $this->getUid();
            //等待同步
            $postsScheme->setSynching(); // 设置目前正在同步
            try {
                $post = $postsScheme->getPost();
                if (empty($postsScheme->third_id)) {
                    //首次同步
                    $res = $this->sendPost($post->title, $post->contents);
                    if ($res) {
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->third_url = $res['url'];
                        return $postsScheme->setSynced(); //设置已经同步完成
                    }
                } else {
                    // 更新同步
                    $res = $this->sendPost($post->title, $post->contents, $postsScheme->third_id);
                    if ($res) {
                        $postsScheme->third_id = $res['id'];
                        $postsScheme->setSynced(); //设置已经同步完成
                    }
                }
            } catch (Exception $exception) {
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
                'json'    => ['delta_time' => 0, 'title' => $title],
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $this->getCookie(),
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "发表博文时同步时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            throw new Exception($error);
        } catch (BadResponseException $exception) {
            throw new Exception($exception->getMessage());
        }
        $response = $response->getBody()->getContents();
        $data = json_decode($response, true);
        if (is_array($data) && isset($data['id'])) {
            return $data['id'];
        }
        throw new Exception("创建草稿时遇到错误，响应与预期不符。 response: " . $response);
    }

    /**
     * 更新文章
     * @param $title
     * @param $content
     * @param null $thirdId
     * @return array
     * @throws Exception
     */
    protected function sendPost($title, $content, $thirdId = null)
    {
        if (!$thirdId) {
            //新文章
            $id = $this->createDrafts($title);
        } else {
            $id = $thirdId;
        }

        // 更新草稿
        if ($this->updateDrafts($id, $this->toHtml($content), $title)) {
            echo "草稿更新成功" . PHP_EOL;
        }

        // 发布
        $this->publish($id);
        return ['id' => $id, 'url' =>  'https://zhuanlan.zhihu.com/p/' . $id];
    }

    /**
     * 发布文章
     * @param $articleId
     * @throws Exception
     */
    protected function publish($articleId)
    {
        $url = sprintf("https://zhuanlan.zhihu.com/api/articles/%s/publish", $articleId);
        $client = new Client();
        try {
            $response = $client->request('PUT', $url, [
                'json' => [
                    'column' => null,
                    'commentPermission' => "anyone"
                ],
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $this->getCookie(),
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "发布文章时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            throw new Exception($error);
        } catch (BadResponseException $exception) {
            throw new Exception($exception->getMessage());
        }
        $statusCode = $response->getStatusCode();
        if($statusCode !== 200){
            throw new Exception("发布文章遇到错误，响应结果与预期不符!");
        }
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
        $markdown = preg_replace_callback('/\!\[[^\]]*\]\(([^\)]+)\)/is', function ($matches) {
            return str_replace($matches[1], $this->pushImagesToZhihu($matches[1]), $matches[0]);
        }, $markdown);
        $Parsedown = new Parsedown;
        $html = $Parsedown->text($markdown);

        // 剔除多余换行
        // 知乎有个问题，通过自定义转换后的HTML5文章，会将换行符转换为<p><br></p>，产生很多换行影响文体。
        $html = str_replace("\n", '', $html);
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
        $payload = [
            'content'    => $content,
            'delta_time' => 1,
        ];
        if (!empty($title)) $payload['title'] = $title;
        $url = sprintf("https://zhuanlan.zhihu.com/api/articles/%s/draft", $id);
        $client = new Client();
        try {
            $response = $client->request('PATCH', $url, [
                'json'    => $payload,
                'headers' => [
                    'content-type' => 'application/json',
                    'Cookie'       => $this->getCookie(),
                    'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                ]
            ]);
        } catch (ClientException $e) {
            $error = "更新草稿内容时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            throw new Exception($error);
        } catch (BadResponseException $exception) {
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
            $error = "获取草稿内容时遇到错误，HTTP状态码：" . $e->getResponse()->getStatusCode() . " message:" . $e->getMessage();
            Log::error($error);
            throw new Exception($error);
        } catch (BadResponseException $exception) {
            throw new Exception($exception->getMessage());
        }
        if ($response->getStatusCode() == 200) {
            $data = $response->getBody()->getContents();
            $data = json_decode($data, true);
            return Arr::get($data, 'content');
        }
        throw new Exception("获取草稿内容时失败，HTTP响应状态：" . $response->getStatusCode());
    }

    /**
     * 同步图片到知乎
     * @param $imgUrl
     * @return mixed
     * @throws Exception
     */
    protected function pushImagesToZhihu($imgUrl)
    {
        // 获取上传图片ID和KEY  POST https://api.zhihu.com/images
        // image_hash
        $image = $this->getImagesToLocal($imgUrl);
        $client = new Client();
        $response = $client->request('POST', "https://api.zhihu.com/images", [
            'json'    => ['image_hash' => $image['hash'], 'source' => 'article'],
            'headers' => [
                'content-type' => 'application/json',
                'Cookie'       => $this->getCookie(),
                'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);//  {"upload_file": {"image_id": "121290**********", "state": 1}}
        if (!$data || !isset($data['upload_file']['image_id'])) {
            throw new Exception("第一步获取图片的HASH状态响应结果异常!");
        }

        if ($data['upload_file']['state'] == 1) {
            // 已经存在图片
            $imageId = $data['upload_file']['image_id'];
        } else if ($data['upload_file']['state'] == 2) {
            $imageId = $data['upload_file']['image_id'];
            $accessToken = $data['upload_token']['access_token'];
            $accessId = $data['upload_token']['access_id'];
            $accessKey = $data['upload_token']['access_key'];
            $objectKey = $data['upload_file']['object_key'];
            // 需要新上传
            // 上传图片 PUT https://zhihu-pics-upload.zhimg.com/v2-2e24eb2eae06b16828f6e83e319f6aaf=
            $this->pushObject($image, $objectKey, $accessToken, $accessKey, $accessId);

            // 设置图片状态完成
            $this->setImageStatus($imageId);
        } else {
            throw new Exception("第一步获取图片的HASH状态响应结果与预期不符!");
        }

        // 通过ImageId获取图片外链
        $imageDetail = $this->getImagesDetail($imageId);
        if(is_string($imageDetail) && $imageDetail=='processing')
        {
            // 再次尝试设置并获取
            $this->setImageStatus($imageId);
            $imageDetail = $this->getImagesDetail($imageId);
            if($imageDetail == 'processing'){
                throw new Exception("图片还在处理中，异常情况！");
            }
        }
        return $imageDetail['original_src'];
    }

    /**
     * 将图片缓存到本地
     * @param $url
     * @return array
     */
    protected function getImagesToLocal($url)
    {
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];
        $img = file_get_contents($url,
            false, stream_context_create($stream_opts));
        $urlInfo = parse_url($url);
        $pathInfo = pathinfo($urlInfo['path']);
        $ext = $pathInfo['extension'] ?? 'jpg';
        Storage::disk('local')->put('download/' . md5($url) . ".{$ext}", $img);
        return ['path' => 'download/' . md5($url) . ".{$ext}", 'hash' => md5($img)];
    }

    /**
     * 上传图片
     * @param array $image
     * @param $objectKey
     * @param $accessToken
     * @param $accessKey
     * @param $accessId
     * @throws Exception
     */
    protected function pushObject(array $image, $objectKey, $accessToken, $accessKey, $accessId)
    {
        $date = gmdate("D, d M Y H:i:s") . " GMT";
        try {
            $client = new Client();
            $response = $client->request('PUT', "https://zhihu-pics-upload.zhimg.com/" . $objectKey, [
                'body'    => Storage::get($image['path']),
                'headers' => [
                    'authorization'        => $this->signature($date, $accessToken, $objectKey, $accessKey, $accessId),
                    'content-type'         => 'image/jpeg',
                    'user-agent'           => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
                    'Origin'               => 'https://zhuanlan.zhihu.com',
                    'Referer'              => 'https://zhuanlan.zhihu.com',
                    'x-oss-date'           => $date,
                    'x-oss-security-token' => $accessToken,
                    'x-oss-user-agent'     => "aliyun-sdk-js/6.1.1 Chrome 75.0.3770.100 on Windows 10 64-bit",
                ],
            ]);
        } catch (ClientException $exception) {
            echo $exception->getMessage();
            echo $exception->getResponse()->getBody()->getContents();
        }

        if ($response->getStatusCode() != 200) {
            throw new Exception("上传图片没有成功!");
        }
    }

    /**
     * 设置图片状态为完成
     * @param $imageId
     * @throws Exception
     */
    protected function setImageStatus($imageId)
    {
        $client = new Client();
        echo "更新图片状态：" . sprintf('https://api.zhihu.com/images/%s/uploading_status', $imageId) . PHP_EOL;
        // 更新图片状态 PUT https://api.zhihu.com/images/1211861351078940672/uploading_status

        $response = $client->request('PUT', sprintf('https://api.zhihu.com/images/%s/uploading_status', $imageId), [
            'json'    => ['upload_result' => "success"],
            'headers' => [
                'content-type' => 'application/json',
                'Cookie'       => $this->getCookie(),
                'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            // 设置图片状态没有成功
            throw new Exception("设置图片状态没有成功!");
        }
        echo "设置图片状态:".$statusCode.PHP_EOL;
        echo "响应：".$response->getBody()->getContents();
    }

    /**
     * 获取图片外链
     * @param $imageId
     * @return mixed
     * @throws Exception
     */
    protected function getImagesDetail($imageId)
    {
        // 获取图片外链 https://api.zhihu.com/images/1211861351078940672
        $client = new Client();
        $response = $client->request('GET', 'https://api.zhihu.com/images/' . $imageId, [
            'headers' => [
                'content-type' => 'application/json',
                'Cookie'       => $this->getCookie(),
                'user-agent'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
            ]
        ]);
        $zhihuImage = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        if (!isset($zhihuImage['status'])) {
            var_dump($zhihuImage);
            throw new Exception("最后一步获取图片信息解析失败!");
        }
        if($zhihuImage['status'] == 'processing'){
            return "processing";
        }
        //原图：original_src 水印图：watermark_src
        return $zhihuImage;
    }

    /**
     * 签名
     * @param $date
     * @param $token
     * @param $objectKey
     * @param $AccessKeySecret
     * @param $accessKeyId
     * @return string
     */
    protected function signature($date, $token, $objectKey, $AccessKeySecret, $accessKeyId)
    {
        // date必须为格林威治时区，如：Tue, 18 Feb 2020 06:57:47 GMT
        // $date = gmdate ("l d F Y H:i:s");
        $input = "PUT\n\nimage/jpeg\n{$date}\nx-oss-date:{$date}\nx-oss-security-token:{$token}\nx-oss-user-agent:aliyun-sdk-js/6.1.1 Chrome 75.0.3770.100 on Windows 10 64-bit\n/zhihu-pics/{$objectKey}";
        $signature = base64_encode(hash_hmac('sha1', $input, $AccessKeySecret, true));
        return 'OSS ' . $accessKeyId . ':' . $signature;
    }



}