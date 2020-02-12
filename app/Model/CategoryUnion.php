<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CategoryUnion extends Model
{
    protected $table = "categories_union";
    protected $dateFormat = 'U';
    protected $fillable = ['platform_id', 'site_cat_id', 'platform_cat_id', 'platform_cat_name'];

    /**
     * 创建关联分类
     * @param array $data
     * @return mixed
     */
    public static function createUnionCategory(array $data)
    {
        $union = self::where([
            'platform_id' => $data['platform_id'],
            'site_cat_id' => $data['site_cat_id'],
        ])->first();
        if(!$union){
            $unionCategory = self::create($data);
            return $unionCategory;
        }
        $union->platform_cat_id = $data['platform_cat_id'];
        $union->platform_cat_name = $data['platform_cat_name'];
        return $union->save();
    }

    /**
     * 获取平台分类列表
     * @param $id
     * @return mixed
     */
    public static function getCategoryListForPlatform($id)
    {
        return self::leftJoin('categories', 'site_cat_id', '=', 'categories.id')
            ->where('platform_id', $id)
            ->select("categories_union.id", "categories_union.platform_id", "categories_union.site_cat_id",
                "categories_union.platform_cat_id", "categories_union.platform_cat_name","categories.name"
            )
            ->get();
    }
}
