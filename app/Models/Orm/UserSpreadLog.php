<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 * 用户推广流水
 * Class UserSpreadLog
 * @package App\Models\Orm
 */
class UserSpreadLog extends AbsBaseModel
{
    /*
     *
     * 设置表名
     */

    const TABLE_NAME = 'sd_user_spread_log';
    const PRIMARY_KEY = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $incrementing = true;
    protected $table = self::TABLE_NAME;
    //主键id
    protected $primaryKey = self::PRIMARY_KEY;
    //查询字段
    protected $visible = [];
    //加黑名单
    protected $guarded = [];

}
