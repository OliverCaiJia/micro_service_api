<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 系统配置模块中使用的常量
 */
class ConfigConstant extends AppConstant
{
    //额外奖金值
    const CONFIG_EXTRA = 'con_extra_reward_remark';
    //默认空
    const DEFAULT_EMPTY = '';
    //诱导轮播 申请人数
    const CONFIG_APPLY = 'con_product_apply_plus';

    //android过审渠道汇总
    const ANDROID_PENDING_CHANNEL = [
        'vivo',
        'huawei',
    ];

    //对接平台appkey常量定义
    const CONFIG_APPKEY = [
        'scorpio' => [
            'appkey' => PRODUCTION_ENV ? '12061ec0445e4fbea3e4e8a7c3' : 'a7d197f11f0a0dfe76a5e648',
        ],
        'ppd_ios' => [
            'appkey' => 'MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAYuCYKrzFzYVkHGP9Fz2UtCTxUklF7JDSBq1xliynwcRiFANax3TxJEiALl3RgDdpfinuQkJqYkxJqk2ObeMvbCUS2Qp42JAJCV2gGFwXyvGPTLW76u0sC9avg5yHTAJSnKRKP22oz3v4tXAfREKx1Dx5uJK0xs5hXJAgMBAAECgYBmBeicWUlyeKIpqGvwSy4ndugmIUyTSAYmQvfO9GZVablc7KJ4erMH8Gslo1fa4B2PR8ScINE++5ISnNKUGlaja8/Av6LvHBudgUhEmiVkAI4VNYujKH/Etk8LDTe1JbCveKJhWxV0sd9mkEjDwOf5QD171Bh6M6Y5SjKl4H8JKQJBAMDsDkzVbrOtwRp/k3rQ3i8QFf1HKcSwjY6T7mcEQoToXNm5v6MYiXRRjof2hXoLZgldyTFtsWygBDzGL1bWk2MCQQC8UYn34L/jMrBYLLRk3919kOlk7/KAJ1LUCxBMT/l1btVE0GUJGn5/reZ/ywBzafQ/UHJPdCF/MVg+iiCJJpfjAkEAgrQrKBD99EvG5V4DnBTAQyBh1XwOJ0z6StucjDzNmGAY8AWxeR0Zmy3aI/F4EuyAD95zfcJ0j0SGOmqHDg1IQwJAHP/BazZk1iu8FBfuP4pppShniG+avDlR++0oOVgnZfoHRW2B4YD+8dJpqEwuaZdrUUSmFa4gamHC4P6MPBKBWQJBAI+8lc6CNjBY7lJgFYxL8MFfwuamSiZ8WmJ8OV44bmFAWNDgP5lIQfhM=',
        ],
        'ppd_android' => [
            'appkey' => '
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAIHTY7WV4t6hu2RTujqBgFZGTsV2iC09lRZK6BndgGCgv6vYs33MFIv7Sjc1fALK1xq8a5owfyze0XcnBAY/QCPs/Ki4LpiDh1N/ahJkFpBovfpjHfWyh1kYtUhWl8U/6XDvFO8lVCJ4c0f/FRSOIXQoUfAgMBAAECgYA195SsSCj+YN40FdVC9a/SjcOiUW3O5T70Yryed6dbGK/hHvlHjkEnFXRy91e30Rx3wdn/culWAtgQsy8fTnBUB69xDUOszkVvqs45+c14Yo6TXV4jLFNX1Ln4ETGw7GAgyMroeZtgvNzq8n2YRn6MFU1NBgbXj0t7zQDD4JcfSQJBAO2T06PXL1PT3LTowNTVvSZPBvyL8Luk1wipYGLrVEMFjPH/3UUZBuQStUDxSi+CFhkwB1PoharWuaZr6csjk8sCQQCL5JKxmxLhwMgb4ApKiWMULpif2dXeGuN0bKI5B8rvmgvOJwG8/lhKUZKDFDIbKhbfVP8bNirayyOvXkz6nLF9AkBOzG4w9IltC2Mz2dNDhJUVJLcTgrLY+gach9lBVf5/sFKcXZoddfyUHyRhIubRNRtxRT3Y9dGH5Wp0KWzFBMrbAkBD6CfBaTqINbGtvlqghgJ/eIMEDZVApKLHEDjLIFpFZuzWHJ8+Y+Tt5p0NXg9YmlzR+ot/ZRL2dGeVKFyLxuZtAkEAsSw9kWyRgBjjRCKuGCtgHXGx2PyQTZfDZDQaVdERLsR8KdY9wvgZRpQrKQLAilnWA==',
        ],
    ];

}

