<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
 * Area
 */
class ProductTag extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_product_tag';
    const PRIMARY_KEY = 'product_tag_id';

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

    public function seoTag()
    {
        return $this->hasOne('App\Models\Orm\TagSeo','id','tag_id');
    }
}
