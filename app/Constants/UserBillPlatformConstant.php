<?php

namespace App\Constants;

use App\Constants\AppConstant;

/**
 * 用户账单模块常量
 * Class UserBillConstant
 * @package App\Constants
 */
class UserBillPlatformConstant extends AppConstant
{
    //信用卡银行列表唯一标识
    const BILL_PLATFORM_BANKS = 'bill_platform';

    //网银导入
    const BILL_IMPORT_CYBER_BANK = 'bill_import_cyber_bank';

    //银行默认北京颜色
    const BANK_BG_DEFAULT_COLOR = '2273B9';

    //添加信用卡限制个数
    const BANK_CREDITCARD_COUNT = 15;

    //负债分析 饼状图 颜色
    const BILL_ANALYSIS_BG_COLORS = [
        '#25aefc', '#fb4669', '#ffad29', '#d3e612', '#556fff',
    ];

    //信用卡每日限制导入次数
    const BILL_IMPORT_DAY_COUNT = 2;

    //账单日、还款日差值
    const BILL_DIFFER_VALUE = 18;

    //网贷产品数据 20
    const BILL_PLATFORM_PRODUCTS = [
        [
            'product_id' => 1,
            'product_name' => '我来贷',
        ],
        [
            'product_id' => 2,
            'product_name' => '宜人贷',
        ],
        [
            'product_id' => 3,
            'product_name' => '功夫贷',
        ],
        [
            'product_id' => 4,
            'product_name' => '人品贷极速版',
        ],
        [
            'product_id' => 5,
            'product_name' => '捷信超贷',
        ],
        [
            'product_id' => 6,
            'product_name' => '融360',
        ],
        [
            'product_id' => 7,
            'product_name' => '小赢卡贷',
        ],
        [
            'product_id' => 8,
            'product_name' => '360借条',
        ],
        [
            'product_id' => 9,
            'product_name' => '水象云',
        ],
        [
            'product_id' => 10,
            'product_name' => '中腾信',
        ],
        [
            'product_id' => 11,
            'product_name' => '小树时代',
        ],
        [
            'product_id' => 12,
            'product_name' => '玖富万卡',
        ],
        [
            'product_id' => 13,
            'product_name' => '恒易贷',
        ],
        [
            'product_id' => 14,
            'product_name' => '及贷',
        ],
        [
            'product_id' => 15,
            'product_name' => '速贷',
        ],
        [
            'product_id' => 16,
            'product_name' => '魔借',
        ],
        [
            'product_id' => 17,
            'product_name' => '金牛贷',
        ],
        [
            'product_id' => 18,
            'product_name' => '51卡宝',
        ],
        [
            'product_id' => 19,
            'product_name' => '乐贷款',
        ],
        [
            'product_id' => 20,
            'product_name' => '厚钱包',
        ],
        [
            'product_id' => 0,
            'product_name' => '自定义',
        ],
    ];

    //第二版网贷产品数据 27
    const BILL_PLATFORM_PRODUCTS_RENEW = [
        [
            'product_id' => 8,
            'product_name' => '360借条',
        ],
        [
            'product_id' => 2,
            'product_name' => '宜人贷',
        ],
        [
            'product_id' => 6,
            'product_name' => '融360',
        ],
        [
            'product_id' => 12,
            'product_name' => '玖富万卡',
        ],
        [
            'product_id' => 4,
            'product_name' => '人品贷极速版',
        ],
        [
            'product_id' => 21,
            'product_name' => '环球黑卡',
        ],
        [
            'product_id' => 7,
            'product_name' => '小赢卡贷',
        ],
        [
            'product_id' => 16,
            'product_name' => '魔借',
        ],
        [
            'product_id' => 5,
            'product_name' => '捷信超贷',
        ],
        [
            'product_id' => 22,
            'product_name' => '拍拍贷',
        ],
        [
            'product_id' => 3,
            'product_name' => '功夫贷',
        ],
        [
            'product_id' => 20,
            'product_name' => '厚钱包',
        ],
        [
            'product_id' => 14,
            'product_name' => '及贷',
        ],
        [
            'product_id' => 17,
            'product_name' => '金牛贷',
        ],
        [
            'product_id' => 23,
            'product_name' => '新浪有还',
        ],
        [
            'product_id' => 24,
            'product_name' => '任信用',
        ],
        [
            'product_id' => 25,
            'product_name' => '小黑鱼',
        ],
        [
            'product_id' => 9,
            'product_name' => '水象云',
        ],
        [
            'product_id' => 26,
            'product_name' => '钱进袋',
        ],
        [
            'product_id' => 27,
            'product_name' => '人人贷',
        ],
        [
            'product_id' => 0,
            'product_name' => '自定义',
        ],
    ];
}

