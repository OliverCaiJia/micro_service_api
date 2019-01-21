<?php
/**
 * Created by PhpStorm.
 * User: sudai
 * Date: 18-1-18
 * Time: 上午11:02
 */
namespace App\Services\Core\Data\Heiniu\Config;

class HeiniuConfig {
    // url
    const URL = PRODUCTION_ENV ? 'http://www.heiniubao.com/insurance/enhanced' : 'http://47.92.104.7:909/insurance/enhanced';
    // channel
    const CHANNEL = 'jdt';
    // subchannel
    const SUBCHANNEL = 'jdt1';
    // des key
    const DES_KEY = 'a1d39c';
}