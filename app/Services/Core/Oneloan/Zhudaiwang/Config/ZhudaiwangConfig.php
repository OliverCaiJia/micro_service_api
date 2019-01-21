<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Oneloan\Zhudaiwang\Config;

class ZhudaiwangConfig {
    // url
    const URL = 'https://jiekou.zhudai.com/sem/loan_do.html';
    // source
    const SOURCE = 'safew';

    /**
     * 获取信息
     * @param int $code
     * @return mixed|string
     */
    public static function getMessage($code = 0){
        $arr = [
            3 => '指定时间内重复申请(请和运营协商时间)',
            5 => '失败',
            6 => '恶意ip',
            7 => '恶意电话',
        ];
        $message = '';
        if ($code > 1000000) {
            $message = '成功';
        } elseif(isset($arr[$code])) {
            $message = $arr[$code];
        } else {
            $message = '不再接受数据';
        }

        return $message;
    }
}