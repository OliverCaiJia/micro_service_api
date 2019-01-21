<?php

// https://github.com/aishan/lumen-captcha
return [
    'useful_time' => 5, //验证码有效时间，单位（分钟）
    'captcha_characters' => '2346789abcdefghjmnpqrtuxyzABCDEFGHJMNPQRTUXYZ',
    'sensitive' => false, //验证码大小写是否敏感
    'login' => [//登陆验证码样式
        'length' => 4, //验证码字数
        'width' => 120, //图片宽度
        'height' => 36, //字体大小和图片高度
        'angle' => 10, //验证码中字体倾斜度
        'lines' => 2, //生成横线条数
        'quality' => 90, //品质
        'invert' => false, //反相
        'bgImage' => true, //是否有背景图
        'bgColor' => '#ffffff',
        'blur' => 0, //模糊度
        'sharpen' => 0, //锐化
        'contrast' => 0, //反差
        'fontColors' => ['#339900', '#ff3300', '#9966ff', '#3333ff'], //字体颜色
    ]
];

