<?php

namespace App\Services\Core\Oneloan\Xinyidai\Config;

class XinyidaiConfig
{
    // 正式线
    const FORMAL_URL = 'https://rsb.pingan.com.cn/brop/ma/cust/app/market/loan/applyCarLoan.do';
    // 测试线
    const TEST_URL = 'https://rsb-stg.pingan.com.cn/brop/ma/cust/app/market/loan/applyCarLoan.do';
    //地址
    const URL = PRODUCTION_ENV ? self::FORMAL_URL : self::TEST_URL;
    // source
    const SOURCE = 'sa340463';
    // outerSource
    const OUTERSOURCE = 'os003441';
    // outerid
    const OUTERID = 'ou0340220';
    // cid
    const CID = 'ciwr4001';
}