<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
 * TagSeo
 */
class TagSeo extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_tag_seo';
    const PRIMARY_KEY = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;
    //主键id
    protected $primaryKey = self::PRIMARY_KEY;
    //查询字段
    protected $visible = [];
    //加黑名单
    protected $guarded = [];

    public function productTag()
    {
        return $this->hasMany('App\Models\Orm\ProductTag','id','tag_id');
    }
}
