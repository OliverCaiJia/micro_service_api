<?php

$router->group(['prefix' => 'v5', 'namespace' => 'V5', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {

        //产品列表 & 速贷大全筛选
        $router->get('', ['uses' => 'ProductController@fetchProductsOrSearchs']);
        //产品详情细节 第二部分
        $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductLike']);

    });


});

