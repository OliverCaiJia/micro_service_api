<?php

$router->group(['prefix' => 'v3', 'namespace' => 'V3', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {
    /*
     *  Users API
     */
    $router->group(['prefix' => 'users'], function ($router) {
        //修改用户名
        $router->post('username', ['middleware' => ['validate:username', 'auth'], 'uses' => 'UserController@updateUsername']);


        $router->post('', ['uses' => 'UserController@create']);
        $router->put('{id}', ['uses' => 'UserController@update']);
        $router->delete('{id}', ['uses' => 'UserController@delete']);
        $router->get('', ['uses' => 'UserController@index']);
    });

    /**
     *  Banners API
     */
    $router->group(['prefix' => 'banners'], function ($router) {
        //分类专题
        $router->get('special', ['uses' => 'BannersController@fetchSpecialsAndRecommends']);
    });

    /**
     *  Product API
     */
    $router->group(['prefix' => 'products'], function ($router) {
        //计算器
        $router->get('calculator', ['middleware' => ['validate:productdetail', 'validate:calculator'], 'uses' => 'ProductController@fetchCalculators']);
        //产品列表 & 速贷大全筛选
        $router->get('', ['uses' => 'ProductController@fetchProductsOrSearchs']);
        //产品详情第一部分
        $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailPartOne']);
        //产品详情细节 第二部分
        $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetailOther']);

    });

    /**
     *  Comment API
     */
    $router->group(['prefix' => 'comment'], function ($router) {
        //查询评论内容
        $router->get('before', ['middleware' => ['validate:productdetail', 'auth'], 'uses' => 'CommentController@fetchCommentsBefore']);
        //修改评论内容
        $router->post('', ['middleware' => ['validate:comment', 'auth'], 'uses' => 'CommentController@createOrUpdateComment']);
        //评论星星值
        $router->get('score', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentCountAndScore']);
        //最热评论
        $router->get('hots', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentHots']);
        //所有评论
        $router->get('comments', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchComments']);
    });
});

