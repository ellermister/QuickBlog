<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PostsSchemes extends Model
{
    protected $dateFormat = 'U';
    protected $fillable = ['post_id', 'platform_id', 'sync_status'];


    /**
     * 创建计划
     * @param Platforms $platform
     * @param $id
     * @return int
     */
    public static function createSchemes(Platforms $platform)
    {
        $posts = Post::all();
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
}
