<?php

namespace App\Models\Orm;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use App\Models\AbsBaseModel;
use Laravel\Passport\HasApiTokens;

class User extends AbsBaseModel implements AuthenticatableContract, AuthorizableContract
{

    use Authenticatable,
        Authorizable,
        HasApiTokens;

    // 表名
    const TABLE_NAME = 'sd_user_auth';
    const PRIMARY_KEY = 'user_id';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'phone',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

}
