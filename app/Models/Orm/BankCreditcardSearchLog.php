<?php

namespace App\Models\Orm;

use App\Models\AbsBaseModel;

/**
 *
 * 信用卡搜索流水记录表
 */
class BankCreditcardSearchLog extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_bank_creditcard_search_log';
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
