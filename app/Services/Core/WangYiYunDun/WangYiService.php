<?php

namespace App\Services\Core\WangYiYunDun;

/**
 * 外部Http Service服务调用 
 */
class WangYiService
{
    /** 易盾内容安全服务图片在线检测接口地址 */
    const API_URL = "https://as.dun.163yun.com/v3/image/check";
    /** 易盾内容安全服务文本在线检测接口地址 */
    const TEXT_URL = "https://as.dun.163yun.com/v3/text/check";
    /** 产品密钥ID，产品标识 */
    const SECRETID = '9c2bc786652034b2e39csdfa164079e86d4';
    /** 产品私有密钥，服务端生成签名信息使用，请严格保管，避免泄露 */
    const SECRETKEY = 'eab5e0dde2270dsaf010192ea9d25eddf9ff';
    /** 图片产品ID，易盾根据产品业务特点分配 */
    const BUSINESSID = 'aeeed4f7asf49f98ff6b6558134df8a4340';
    /** 用户名产品ID，易盾根据产品业务特点分配 */
    const TEXTBUSINESSID = 'ed609sdafe9b35a84ea2709b564b5b9073e5';
    /** 评论产品ID，易盾根据产品业务特点分配 */
    const REPLYBUSINESSID = '647c8d4d7sdafb2106d2684df63ccad9817f';

    const INTERNAL_STRING_CHARSET= 'auto';

    const VERSION='v3.2';

}
