<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/*
 *
 * Banner 
 */
class Banner extends AbsBaseModel
{
    /*
     *
     * 设置表名
     */

    const TABLE_NAME = 'sd_banner';

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
