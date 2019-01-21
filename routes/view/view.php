<?php

$router->group(['prefix' => 'view', 'namespace' => 'View', 'middleware' => ['analysis', 'sign', 'cros']], function ($router) {
    /*
     *  Users API
     */
    $router->group(['prefix' => 'users'], function ($router) {

        //身份认证
        $router->group(['prefix' => 'identity'], function ($router) {
            //身份认证——认证协议
            $router->get('agreement', ['uses' => 'UserController@fetchIdentityAgreement']);
            //——会员协议
            $router->get('membership', ['uses' => 'UserController@fetchMembershipAgreement']);
            //APP用户使用协议
            $router->get('use', ['uses' => 'UserController@fetchUseAgreement']);
            // 畅行天下升级版保险文档说明
            $router->get('insurance', ['uses' => 'UserController@fetchChangxingtianxiaAgreement']);
        });

        //信用报告
        $router->group(['prefix' => 'report'], function ($router) {
            //信用报告——个人报告查询授权书
            $router->get('agreement', ['uses' => 'UserReportController@fetchReportAgreement']);
            //信用报告样本
            $router->get('sample', ['uses' => 'UserReportController@fetchReportSample']);
            //信用报告详情
            $router->get('info', ['middleware' => ['auth'], 'uses' => 'UserReportController@fetchReportinfo']);
            //自定义芝麻跳转地址
            $router->get('zhima/url', ['uses' => 'UserReportController@fetchZhimaUrl']);
        });

        //贷款账户

        $router->group(['prefix' => 'bill'], function ($router) {
            //账户数据分析
            $router->get('analysis', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchBillAnalysis']);
            //账单明细
            $router->get('creditcard/bills', ['middleware' => ['auth', 'validate:billCreditcardId'], 'uses' => 'UserBillController@fetchCreditcardBills']);
            //导入信用卡账单结果列表
            $router->get('import/results', ['middleware' => ['auth'], 'uses' => 'UserBillController@fetchBillImportResults']);
        });


    });

    /**
     *  Help API
     */
    $router->group(['prefix' => 'help'], function ($router) {
        //设置 - 协议列表
        $router->get('agreements', ['uses' => 'HelpController@fetchAgreements']);
        //设置 - 协议详情
        $router->get('agreement', ['middleware' => ['validate:id'], 'uses' => 'HelpController@fetchAgreement']);
    });

    /**
     *  Oneloan API
     */
    $router->group(['prefix' => 'oneloan'], function ($router) {
        //基础信息
        $router->get('basic', ['uses' => 'OneloanController@fetchBasic']);
        //完整信息
        $router->get('full', ['uses' => 'OneloanController@fetchFull']);
        //结果
        $router->get('result', ['uses' => 'OneloanController@fetchResult']);
        //公共
        $router->get('common', ['uses' => 'OneloanController@fetchCommon']);
        //协议
        $router->get('agreement', ['uses' => 'OneloanController@fetchAgreement']);
        //城市列表
        $router->get('citys', ['uses' => 'OneloanController@fetchCitys']);
    });
});

