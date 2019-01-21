<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 17-7-31
 * Time: 下午3:11
 */
namespace App\Models\Orm;

use App\Models\AbsBaseModel;
class UserVipPrivilegeRelation extends AbsBaseModel
{
    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_user_vip_privilege_relation';
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