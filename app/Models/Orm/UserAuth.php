<?php

namespace App\Models\Orm;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Models\AbsBaseModel;
use Laravel\Passport\HasApiTokens;

/**
 *
 * Area
 */
class UserAuth extends AbsBaseModel implements AuthenticatableContract, AuthorizableContract
{

    use HasApiTokens,
        Authenticatable,
        Authorizable;

    /**
     *
     *  设置表名
     */
    const TABLE_NAME = 'sd_user_auth';
    const PRIMARY_KEY = 'sd_user_id';
	
	
	public $incrementing = true;
	
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
    //隐藏字段
    //protected $hidden = ['password'];

}
