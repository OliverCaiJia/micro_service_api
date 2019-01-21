<?php

$router->group(['prefix' => 'v2', 'namespace' => 'V2', 'middleware' => ['sign', 'cros', 'analysis']], function ($router) {

    /**
     *   Auth API
     */
    $router->group(['prefix' => 'auth'], function ($router) {
        // 快速登陆
        $router->post('quicklogin', ['middleware' => ['validate:quicklogin'], 'uses' => 'AuthController@quickLogin']);
    });


    /*
     *  Users API
     */
    $router->group(['prefix' => 'users'], function ($router) {
        //修改用户名
        $router->post('username', ['middleware' => ['validate:username', 'auth'], 'uses' => 'UserController@updateUsername']);
        //上传用户头像
        $router->post('photo', ['middleware' => ['auth', 'validate:photo'], 'uses' => 'UserController@uploadPhoto']);
        //用户账户信息
        $router->get('info', ['middleware' => ['auth'], 'uses' => 'UserController@fetchUserinfo']);

        //信用报告
        $router->group(['prefix' => 'report'], function ($router) {

            //信用报告订单列表
            $router->get('reports', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchReports']);
        });

        // VIP
        $router->group(['prefix' => 'vip'], function ($router) {
            //会员中心
            $router->get('centers', ['uses' => 'UserVipController@memberCenter']);
        });

    });

    /**
     *  Banners API
     */
    $router->group(['prefix' => 'banners'], function ($router) {
        //广告轮播
        $router->get('', ['uses' => 'BannersController@fetchBanners']);
        //分类专题
        $router->get('special', ['uses' => 'BannersController@fetchSpecials']);
        //速贷推荐
        $router->get('subjects', ['uses' => 'BannersController@fetchSubjects']);
        //会员中心广告
        $router->get('vip/center', ['uses' => 'BannersController@fetchVipCenterBanner']);
        //置顶分类专题
        $router->get('special/tops', ['uses' => 'BannersController@fetchSpecialTops']);
    });

    /**
     *  Comment API
     */
    $router->group(['prefix' => 'comment'], function ($router) {
        //最热评论
        $router->get('hots', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchCommentHots']);
        //所有评论
        $router->get('comments', ['middleware' => ['validate:productdetail'], 'uses' => 'CommentController@fetchComments']);
        //修改评论内容
        $router->post('', ['middleware' => ['validate:comment', 'auth'], 'uses' => 'CommentController@createOrUpdateComment']);
    });

    /**
     *  Reply API
     */
    $router->group(['prefix' => 'reply'], function ($router) {
        //创建回复
        $router->post('', ['middleware' => ['auth', 'validate:replys'], 'uses' => 'ReplyController@createReply']);
        //回复列表
        $router->get('replys', ['middleware' => ['validate:reply'], 'uses' => 'ReplyController@fetchReplysByCommentId']);
    });

    /**
     *  Product API
     */
    $router->group(['prefix' => 'product'], function ($router) {
        //诱导轮播
        $router->get('promotion', ['uses' => 'ProductController@fetchPromotions']);
        //专题产品
        $router->get('special', ['middleware' => ['validate:special'], 'uses' => 'ProductController@fetchSpecials']);
        //计算器
        $router->get('calculator', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchCalculators']);
        //产品详情 第一部分
        $router->get('detail', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchDetails']);
        //产品详情细节 第二部分
        $router->get('particular', ['middleware' => ['validate:productdetail'], 'uses' => 'ProductController@fetchProductDetails']);
        //速贷大全列表 or 速贷大全筛选
        $router->get('lists', ['uses' => 'ProductController@fetchProductsOrSearchs']);
        //首页推荐产品
        $router->get('recommends', ['uses' => 'ProductController@fetchRecommends']);
        //新上线产品
        $router->get('online', ['uses' => 'ProductController@fetchNewOnlines']);
        //代还信用卡产品
        $router->get('givebacks', ['uses' => 'ProductController@fetchGiveBackProducts']);
        //产品搜索标签
        $router->get('searchtag', ['uses' => 'ProductController@fetchSearchProductTags']);
        //我的申请
        $router->get('historys', ['middleware' => ['auth'], 'uses' => 'ProductController@fetchApplyHistory']);

    });

    /**
     *  ProductSearch API
     */
    $router->group(['prefix' => 'product/searchs'], function ($router) {
        //搜索热刺列表
        $router->get('hots', ['uses' => 'ProductSearchController@fetchHots']);
        //搜索结果列表
        $router->get('', ['middleware' => ['validate:productsearch'], 'uses' => 'ProductSearchController@fetchSearchs']);
    });

    /**
     *  Favourite API
     */
    $router->group(['prefix' => 'favourite'], function ($router) {
        //产品——收藏列表
        $router->get('collections', ['middleware' => ['auth'], 'uses' => 'FavouriteController@fetchCollectionLists']);

    });

    /**
     *  Exact API
     */
    $router->group(['prefix' => 'exact'], function ($router) {
        //获取精确匹配数据
        $router->get('exacts', ['middleware' => ['auth', 'validate:page'], 'uses' => 'ExactController@fetchExactMatchDatas']);

    });

    /**
     *  Geetest API 2.0
     */
    $router->group(['prefix' => 'geetest'], function ($router) {
        // 获取uuid
        $router->get('uuid', ['middleware' => ['api_cros', 'validate:GeetestUuid'], 'uses' => 'GeetestController@getUuid']);
        // 验证uuid
        $router->get('uuid/verification', ['middleware' => ['api_cros', 'validate:GeetestUuidVerify'], 'uses' => 'GeetestController@verifyUuid']);
        // 极验 —— 极验一次验证
        $router->get('captcha', ['middleware' => ['api_cros'], 'uses' => 'GeetestController@fetchCaptcha']);
        //极验 —— 极验二次验证
        $router->post('verification', ['middleware' => ['api_cros', 'validate:GeetestVerification'], 'uses' => 'GeetestController@fetchVerification']);

        // 极验-- WEB测试页面 上线可删除
        $router->get('', ['uses' => 'GeetestController@test']);
    });

    /**
     * Push API
     */
    $router->group(['prefix' => 'push'], function ($router) {
        //极光推送
        $router->get('jpush', ['middleware' => ['validate:push'], 'uses' => 'PushController@fetchJpushRegId']);
        //任务弹窗
        $router->get('popup', ['middleware' => ['validate:popup'], 'uses' => 'PushController@fetchPopup']);
        // 任务弹窗点击统计
        $router->get('count', ['middleware' => ['validate:pushId'], 'uses' => 'PushController@updatePopupCount']);
    });

    /**
     * Help API
     */
    $router->group(['prefix' => 'helps'], function ($router) {
        //帮助中心
        $router->get('', ['uses' => 'HelpController@fetchHelps']);
        // 帮助中心 —— 提问&反馈
        $router->post('feedback', ['middleware' => ['auth', 'api_cros', 'validate:feedback'], 'uses' => 'HelpController@createFeedback']);

    });

    // 我的积分 2.0
    $router->group(['prefix' => 'credit'], function ($router) {
        // 获取积分列表
        $router->get('', ['middleware' => ['auth', 'validate:CreditList'], 'uses' => 'CreditController@fetchCreditIncome']);
    });

    /**
     *  Club API 2.0
     */
    $router->group(['prefix' => 'club'], function ($router) {
        // 速贷之家用户与论坛用户绑定
        $router->get('bind', ['middleware' => ['auth'], 'uses' => 'ClubController@clubBind']);
        $router->post('bind', ['middleware' => ['auth'], 'uses' => 'ClubController@clubBind']);
    });

    /**
     * Data API
     */
    $router->group(['prefix' => 'data'], function ($router) {
        //百融
        $router->post('bairong', ['middleware' => ['validate:bairong'], 'uses' => 'DataController@getBairongQuery']);
        //保险
        $router->post('insurance', ['middleware' => ['auth'], 'uses' => 'DataController@applyInsurance']);
    });


    /**
     * 信用卡 2.0
     */
    $router->group(['prefix' => 'data'], function ($router) {
        /**
         * 信用卡统计 API
         */
        $router->group(['prefix' => 'credit'], function ($router) {
            // 统计信用卡申请点击
            $router->get('apply/log', ['uses' => 'BankCardDataController@applyCount']);
        });
    });

    /**
     *  Oauth API
     */
    $router->group(['prefix' => 'oauth'], function ($router) {
        //立即申请
        $router->get('application', ['middleware' => ['auth', 'validate:oauth'], 'uses' => 'OauthController@fetchLoanmoney']);
    });

    /**
     * Spread API
     */
    $router->group(['prefix' => 'spread'], function ($router) {
        //百款聚到
        $router->get('info', ['uses' => 'UserSpreadController@fetchOneloanInfo']);
    });

});

