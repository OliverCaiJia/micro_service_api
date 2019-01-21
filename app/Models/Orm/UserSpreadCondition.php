<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 * 一键选贷款条件表
 *
 * Class UserSpreadType
 * @package App\Models\Orm
 */

class UserSpreadCondition extends AbsBaseModel
{

    const TABLE_NAME = 'sd_user_spread_condition';
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
