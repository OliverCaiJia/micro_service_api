<?php

return [
    //-------------------合作方对接的------------------------//
    'des_key' => 'u8c9kToN',
    //-----------------七牛图片储存配置-----------------------//
    'qiniu' => [
        'ak' => 'U43-nZtveZk30g2QaBQdYKo_HeSkOZnznppUYr',
        'sk' => 'Zmvxi0RJ1dh2VcA2Fmro3KWQ2zHEIdsvbKuD',
        /*
          'bucket' => 'jdt-test',
          'domain' => 'jdt-test.qiniudn.com',
          'prefix' => '',
          'baseurl' => 'http://obd78f18t.bkt.clouddn.com/', //速贷之家测试线七牛地址
         */
        'bucket' => 'jdt-online',
        'prefix' => '',
        'domain' => 'jdt-online.qiniudn.com',
        'baseurl' => (\App\Helpers\Utils::isiOS() || \App\Helpers\Utils::isMAPI() ) ? 'http://obd7ty4wc.bkt.clouddn.com/': 'http://image.jdt.com/', //速贷之家正式线七牛地址
        //'baseurl' => 'http://obd7ty4wc.bkt.clouddn.com/', //速贷之家正式线七牛地址

    ],
    'imageUrl' => 'http://image.jdt.com/',
    //---------------------盐值-----------------------------//
    'salt' => [
        'salt' => '7edc32332fe11fb3c176408196d86ad206e9a3',
    ],
];
