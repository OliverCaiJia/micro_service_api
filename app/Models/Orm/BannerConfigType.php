<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/*
 *
 * 图片设置图片类型表
 */
class BannerConfigType extends AbsBaseModel
{
    /*
     *
     * 设置表名
     */

    const TABLE_NAME = 'sd_banner_config_type';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    //查询字段
    protected $visible = [];

    //加黑名单
    protected $guarded = [];

}
