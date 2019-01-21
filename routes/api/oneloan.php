<?php

$router->group(['prefix' => 'oneloan', 'namespace' => 'Oneloan', 'middleware' => ['cros', 'sign']], function ($router) {

    /**
     * 一键贷API V1
     */
    $router->group(['prefix' => 'v1', 'namespace' => 'V1'], function ($router) {

        /**
         * Spread API
         */
        $router->group(['prefix' => 'spread'], function ($router) {
            // 修改基础信息
            $router->post('basic', ['middleware' => ['auth', 'validate:userSpreadBasic'], 'uses' => 'UserSpreadController@createOrUpdateBasic']);
            // 修改完整信息
            $router->post('full', ['uses' => 'UserSpreadController@createOrUpdateFullInfo']);
        });

        /**
         * Flow API
         */
        $router->group(['prefix' => 'flow'], function ($router) {
            // 流量分发
            $router->post('', ['uses' => 'SpreadFlowController@spreadDealApi']);
            // 城市存在同步s
            $router->get('areas', ['uses' => 'SpreadFlowController@synUpdateSpreadAreasRel']);
        });

    });
});
