<?php

$router->group(['prefix' => 'v4', 'namespace' => 'V4', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {
        //计算器
        $router->get('calculator', ['middleware' => ['validate:productdetail', 'validate:calculator'], 'uses' => 'ProductController@fetchCalculators']);
        //产品列表 & 速贷大全筛选
        $router->get('', ['uses' => 'ProductController@fetchProductsOrSearchs']);
        //产品详情第一部分 - 速贷大数据
        $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductDatas']);
        //产品详情第二部分 - 产品特色
        $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailProductLike']);

    });

    /**
     *  Comment API
     */
    $router->group(['prefix' => 'comment'], function ($router) {
        //修改评论内容
        $router->post('', ['middleware' => ['validate:comment', 'auth'], 'uses' => 'CommentController@createOrUpdateComment']);
        //最热评论
        $router->get('hots', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentHots']);
        //所有评论
        $router->get('comments', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchComments']);

    });

});

