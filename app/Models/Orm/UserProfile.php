<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
 * Area
 */
class UserProfile extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_user_profile';
    const PRIMARY_KEY = 'sd_profile_id';

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
