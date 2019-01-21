<?php

$router->group(['prefix' => 'v1', 'namespace' => 'V1', 'middleware' => ['cros']], function ($router) {


    /**
     *  Users API
     */
    $router->group(['prefix' => 'callback'], function ($router) {

        // 用户银行卡绑定
        $router->group(['prefix' => 'payment'], function ($router) {
            //易宝回调
            $router->get('yibao/test', ['uses' => 'PaymentCallbackController@test']);
            //同步回调
            $router->get('yibao/syncallbacks', ['uses' => 'PaymentCallbackController@yiBaoSynCallBack']);
            //异步回调
            $router->post('yibao/asyncallbacks', ['uses' => 'PaymentCallbackController@yiBaoAsynCallBack']);

            //同步回调
            $router->post('yibao/syncallbacks', ['uses' => 'PaymentCallbackController@yiBaoSynCallBack']);
            //异步回调
            $router->get('yibao/asyncallbacks', ['uses' => 'PaymentCallbackController@yiBaoAsynCallBack']);
        });
    });

});

