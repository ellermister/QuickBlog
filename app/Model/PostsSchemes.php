<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PostsSchemes extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = ['post_id', 'platform_id', 'sync_status'];

    const SYNC_STATUS = 1; // 就绪

    /**
     * 创建计划
     * @param Platforms $platform
     * @param $id
     * @return int
     */
    public static function createSchemes(Platforms $platform)
    {
        $posts = Post::where('is_sync', 1)->get();
        $count = 0;
        foreach ($posts as $item) {
            $schemes = self::firstOrCreate([
                'post_id'     => $item->id,
                'platform_id' => $platform->id,
            ]);

            // 只有就绪状态下任务方可分配同步
            if ($schemes->sync_status == 0) {
                $schemes->sync_status = 1;
                $schemes->save();
                $count++;
            }
        }
        return $count;
    }

    /**
     * 获取就绪计划
     * @param $name
     * @return mixed|PostsSchemes[]
     */
    public static function getSchemes($name)
    {
        $platform = Platforms::where('name', $name)->first();
        return self::where('platform_id', $platform->id)->where('sync_status', self::SYNC_STATUS)->get();
    }

    /**
     * 获取计划的文章详情
     * @return mixed
     */
    public function getPost()
    {
        return Post::find($this->post_id);
    }

    /**
     * 状态等待同步
     * @return bool
     */
    public function isWaitSyncStatus()
    {
        if($this->sync_status == 1){
            return true;
        }
        return false;
    }

    /**
     * 设置同步中
     * @return bool
     */
    public function setSynching()
    {
        $this->sync_status = 2;
        return $this->save();
    }

    /**
     * 设置同步完成
     * @return bool
     */
    public function setSynced()
    {
        $this->sync_status = 0;
        return $this->save();
    }

    /**
     * 设置同步失败
     * @return bool
     */
    public function setSyncFailed()
    {
        $this->sync_status = 3;
        return $this->save();
    }

    /**
     * 获取关联分类
     * @return array [ name => '', id => '']
     */
    public function getUnionCategory()
    {
        $post = $this->getPost();
        $unionCategory = CategoryUnion::where('platform_id', $this->platform_id)->where('site_cat_id', $post->cat_id)->first();
        if ($unionCategory) {
            return ['name' => $unionCategory->platform_cat_name, 'id' => $unionCategory->platform_cat_id];
        }
        return [];
    }
}
