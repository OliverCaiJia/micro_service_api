<?php

$router->group(['prefix' => 'shadow', 'namespace' => 'Shadow', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {
    $router->group(['prefix' => 'v1', 'namespace' => 'V1'], function ($router) {
        /**
         * 马甲包速贷大全列表
         */
        $router->group(['prefix' => 'product'], function ($router) {
            // 速贷大全列表
            $router->get('lists', ['uses' => 'ProductController@fetchProductsOrSearchs']);
            // 速贷大全列表 & 速贷大选筛选
            $router->get('filters', ['uses' => 'ProductController@fetchProductsOrFilters']);
            // 马甲包产品申请统计
            $router->get('application', ['middleware' => ['auth', 'validate:oauth'], 'uses' => 'ProductController@apply']);
            //产品详情细节 第二部分
            $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailOther']);

        });
        /**d
         *  马甲包快速登录
         */
        $router->group(['prefix' => 'auth'], function ($router) {
            // 快速登陆
            $router->post('quicklogin', ['middleware' => ['validate:quicklogin'], 'uses' => 'AuthController@quickLogin']);
        });

        /**
         * 发送短信
         */
        $router->group(['prefix' => 'sms'], function ($router) {
            //注册短信验证码
            $router->post('register', ['middleware' => ['validate:code'], 'uses' => 'SmsController@register']);
        });

        // 测试用芝麻信用 上线可删除
        $router->group(['prefix' => 'zhima'], function ($router) {
            $router->get('', function () {
                return view('zhima');
            });

            $router->post('query', [
                'as' => 'shadow.zhima',
                'uses' => 'ZhimaController@query',
            ]);

            // 回调地址
            $router->get('score', [
                'as' => '',
                'uses' => 'ZhimaController@getScore',
            ]);
        });
    });
});