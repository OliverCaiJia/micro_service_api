<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 * 用户推广类型表
 * Class UserSpreadType
 * @package App\Models\Orm
 */

class UserSpreadType extends AbsBaseModel
{

    const TABLE_NAME = 'sd_user_spread_type';
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

}
