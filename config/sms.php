<?php

return [
    /**
     *  大汉三通
     */
    'dahansantong' => [
        'account' => 'dh712341',
        'password' => '5DtsdfFJ6E',
        'smsSendUrl' => 'http://www.dht.com/json/sms/Submit',
        'subcode' => 7414,
    ],
    /**
     *  广州短信(创蓝)
     */
    'chuanglan' => [
        //速贷之家
        'smsAccount' => 'Vip_s',
        'smsPassword' => 'jdt2016',
        //'smsSendUrl' => 'http://sapi.3.com/msg/main.do',
        'smsSendUrl' => 'http://222.7.11.158/msg/HttpBatchSendSM',
        //借钱王
        'jqw_smsAccount' => 'vip_jqw1',
        'jqw_smsPassword' => 'Jqw1234',
        //'jqw_smsSendUrl' => 'http://sapi.25.com/msg/main.do',
        'jqw_smsSendUrl' => 'http://222.73.11.18/msg/HttpBatchSendSM',
    ],
    /**
     *  http://c.chanzor.com (畅卓)
     */
    'changzhuo' => [
        'account' => '98e0',
        'password' => '656A67BF43D1864EF0D7418E43622',
        'smsSendUrl' => 'http://api.chanzr.com/send',
    ],
    /**
     * 微网通联
     */
    'wwtl' => [
        'sname' => 'dlzjwkj',
        'spwd' => 'jd016',
        'scorpid' => '',
        'sprdid' => '101218',
        'smsSendUrl' => 'https://seccf.51welink.com/submitdata/service.asmx/g_Submit',
    ],
	
	  /**
	   * 亿美短信通道
	   */
    'yimei' => [
		'cdkey' => '8SDK-EMY-6699-SBYS',
		'password' => '7128',
	    'addserial' => '',
		'smsSendUrl' => 'http://hprpt2.eucp.b2m.cn:880/sdkproxy/sendsms.action',
	]
];
