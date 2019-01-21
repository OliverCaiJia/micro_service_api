<?php

namespace App\Strategies;

use App\Constants\CommentConstant;
use App\Helpers\DateUtils;
use App\Helpers\RestUtils;
use App\Models\Factory\AuthFactory;
use App\Models\Factory\CommentFactory;
use App\Models\Factory\ReplyFactory;
use App\Models\Factory\UserFactory;
use App\Services\Core\Store\Qiniu\QiniuService;
use App\Strategies\AppStrategy;
use Illuminate\Support\Facades\Auth;

/**
 * 评论公共策略
 *
 * @package App\Strategies
 */
class CommentStrategy extends AppStrategy
{
    /**
     * @param array $productArr
     * @param $productId
     * @return array
     * 评论——产品标签+评论分数
     */
    public static function getCommentScore($productArr = [], $productId)
    {
        $datas['product_logo'] = QiniuService::getProductImgs($productArr['product_logo'], $productId);
        $datas['platform_product_name'] = isset($productArr['platform_product_name']) ? $productArr['platform_product_name'] : '';
        $datas['tag_name'] = isset($productArr['tag_name']) ? $productArr['tag_name'] : [];
        $rate = $productArr['composite_rate'];
        $speed = $productArr['loan_speed'];
        $exper = $productArr['experience'];
        $datas['composite_rate'] = isset($rate) ? number_format($rate, 1) : 0;
        $datas['loan_speed'] = isset($speed) ? number_format($speed, 1) : 0;
        $datas['experience'] = isset($exper) ? number_format($exper, 1) : 0;
        $datas['score'] = bcdiv(bcadd($exper, bcadd($rate, $speed)), 3, 1);

        //所有评论个数
        $datas['count_all'] = CommentFactory::commentAllCount($productId);
        //有价值评论个数
        $datas['count_value'] = CommentFactory::commentValueCount($productId);

        return $datas ? $datas : [];
    }

    /**
     * @param array $commentArr
     * @return array
     * 评论——有价值的评论数据
     */
    public static function getCommentValueLists($commentArr = [])
    {
        foreach ($commentArr as $k => $v) {
            $datas[$k]['platform_comment_id'] = $v['platform_comment_id'];
            $datas[$k]['content'] = $v['content'];
            $datas[$k]['score'] = CommentStrategy::getResultScore($v);
            $datas[$k]['result'] = CommentStrategy::resultIntTostr($v['result']);
            $datas[$k]['use_count'] = bcadd($v['use_count'], $v['add_count']);
            $datas[$k]['create_date'] = DateUtils::formatDate($v['create_date']);
            $datas[$k]['username'] = UserFactory::fetchUserName($v['create_id']);
        }

        return $datas ? $datas : [];
    }

    /**
     * @param $comment
     * @param $product
     * 评论——评论内容
     */
    public static function getCommentDatas($comment, $product)
    {
        $comment['content'] = !empty($comment['content']) ? $comment['content'] : '';
        $comment['loan_money'] = !empty($comment['loan_money']) ? $comment['loan_money'] : '';
        $comment['speed'] = !empty($comment['speed']) ? $comment['speed'] : 0;
        $comment['rate'] = !empty($comment['rate']) ? $comment['rate'] : 0;
        $comment['experience'] = !empty($comment['experience']) ? $comment['experience'] : 0;
        $comment['result'] = !empty($comment['result']) ? intval($comment['result']) : 0;
        $comment['product_logo'] = QiniuService::getProductImgs($product['product_logo'], $product['platform_product_id']);
        $comment['platform_product_name'] = !empty($product['platform_product_name']) ? $product['platform_product_name'] : '';
        $comment['platform_id'] = !empty($product['platform_id']) ? $product['platform_id'] : '';
        $comment['result_list'] = CommentStrategy::getResultLists();
        return $comment ? $comment : [];
    }

    /**
     * @param $str
     * @return bool
     * 评论——敏感词过滤
     */
    public static function sensitiveWordFilter($str)
    {
        $sensitive = CommentConstant::SENSITIVE;
        $blacklist = "/" . implode("|", $sensitive) . "/i";
        $bool = preg_match($blacklist, $str, $matches);
        if (preg_match($blacklist, $str, $matches)) {
            return $matches[0];
        } else {
            return false;
        }
    }

    /**
     * @param array $data
     * 评论——修改评论内容
     */
    public static function updateCommentDatas($data = [])
    {
        $result = intval($data['resultId']);
        if ($result == 1 || $result == 5) {
            $data['speed'] = !empty($data['speed']) ? $data['speed'] : 0;
            $data['rate'] = !empty($data['rate']) ? $data['rate'] : 0;
            $data['experience'] = !empty($data['experience']) ? $data['experience'] : 0;
            $data['loan_money'] = !empty($data['loanMoney']) ? $data['loanMoney'] : 0;
        } else {
            $data['speed'] = 0;
            $data['rate'] = 0;
            $data['experience'] = !empty($data['experience']) ? $data['experience'] : 0;
            $data['loan_money'] = 0;
        }
        $data['platform_id'] = intval($data['platformId']);
        $data['content'] = !empty($data['content']) ? $data['content'] : '这个平台不错，利率很低，而且我很快就获得了借款';
        $data['result'] = !empty($data['resultId']) ? intval($data['resultId']) : 1;

        return $data ? $data : [];
    }

    /**
     * @param $data
     * 评论——修改评论内容
     */
    public static function updateComment($data)
    {
        $result = intval($data['resultId']);
        if ($result == 1 || $result == 5) {
            $data['speed'] = !empty($data['speed']) ? $data['speed'] : 0;
            $data['rate'] = !empty($data['rate']) ? $data['rate'] : 0;
            $data['experience'] = !empty($data['experience']) ? $data['experience'] : 0;
            $data['loan_money'] = !empty($data['loanMoney']) ? $data['loanMoney'] : 0;
        } else {
            $data['speed'] = 0;
            $data['rate'] = 0;
            $data['experience'] = !empty($data['experience']) ? $data['experience'] : 0;
            $data['loan_money'] = 0;
        }

        //result == 5 额度**，等我提现  result == 1 借款**元已下款
        if ($result == 1) {
            $data['content'] = !empty($data['content']) ? $data['content'] : '借款' . $data['loan_money'] . '元已下款';
        } elseif ($result == 5) {
            $data['content'] = !empty($data['content']) ? $data['content'] : '额度' . $data['loan_money'] . '，等我提现';
        } else {
            $data['content'] = !empty($data['content']) ? $data['content'] : '这个平台不错，利率很低，而且我很快就获得了借款';
        }

        $data['platform_id'] = intval($data['platformId']);
        $data['result'] = !empty($data['resultId']) ? intval($data['resultId']) : 1;

        return $data ? $data : [];
    }


    ///////////////////////////////////////////////////////////////////////////////////

    /**
     * @param $data
     * @return mixed
     * 获取评论分数加成值
     */
    public static function getResultScore($data)
    {
        $i = $data['result'];
        if ($i == 1 || $i == 5) {
            return bcdiv(bcadd($data['experience'], bcadd($data['rate'], $data['speed'])), 3, 1);
        } else {
            return number_format($data['experience'], 1);
        }
    }

    /**
     * @param $data
     * @return string
     */
    public static function resultIntTostr($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return CommentConstant::RESULT_ONE;
        elseif ($i == 2) return CommentConstant::RESULT_TWO;
        elseif ($i == 3) return CommentConstant::RESULT_THREE;
        elseif ($i == 4) return CommentConstant::RESULT_FOUR;
        elseif ($i == 5) return CommentConstant::RESULT_FIVE;
        elseif ($i == 6) return CommentConstant::RESULT_NOT_APPLY;
        elseif ($i == 7) return CommentConstant::RESULT_APPROVED_LOAN;
        else return CommentConstant::RESULT_FIVE;

    }

    /**
     * @param $data
     * @return string
     * 7已批贷 , 2 被拒绝 , 4 等待审批 , 6 未申请
     */
    public static function resultIntToString($data)
    {
        $i = DateUtils::toInt($data);
        if ($i == 1) return CommentConstant::RESULT_ONE;
        elseif ($i == 2) return CommentConstant::RESULT_TWO;
        elseif ($i == 3) return CommentConstant::RESULT_THREE;
        elseif ($i == 4) return CommentConstant::RESULT_VERSION3_APPLICATION;
        elseif ($i == 5) return CommentConstant::RESULT_FIVE;
        elseif ($i == 6) return CommentConstant::RESULT_VERSION3_NOT_APPLY;
        elseif ($i == 7) return CommentConstant::RESULT_APPROVED_LOAN;
        else return CommentConstant::RESULT_FIVE;
    }

    /**
     * 评论——添加评论申请状态列表
     * result 1，钱到手 2，未通过 3 其他 4 申请中 ， 5 已获批
     * result_sign 代表星星行数 0一行 1三行
     */
    public static function getResultLists()
    {
        $resultLists = [
            [
                'result_id' => 4,
                'result_name' => CommentConstant::RESULT_FOUR,
                'result_sign' => 0,
            ],
            [
                'result_id' => 5,
                'result_name' => CommentConstant::RESULT_FIVE,
                'result_sign' => 1,
            ],
            [
                'result_id' => 1,
                'result_name' => CommentConstant::RESULT_ONE,
                'result_sign' => 1,
            ],
            [
                'result_id' => 2,
                'result_name' => CommentConstant::RESULT_TWO,
                'result_sign' => 0,
            ],
            [
                'result_id' => 3,
                'result_name' => CommentConstant::RESULT_THREE,
                'result_sign' => 0,
            ],

        ];
        return $resultLists ? $resultLists : [];
    }

    /**
     * @param $successCounts
     * @param $failCounts
     * @param $otherCounts
     * @param $applicationCounts
     * @param $approvedCounts
     * @return array
     * const RESULT_SUCCESS = '以下款';
     * const RESULT_FAIL = '被拒绝';
     * const RESULT_OTHER = '其他';
     * const RESULT_APPLICATION = '已申请';
     * const RESULT_APPROVED = '出额度';
     * 评论数量计算
     */
    public static function fetchCommentCounts($successCounts, $failCounts, $otherCounts, $applicationCounts, $approvedCounts)
    {
        $commentCounts = [
            [
                'result_id' => 4,
                'result_name' => CommentConstant::RESULT_APPLICATION,
                'result_count' => $applicationCounts,
            ],
            [
                'result_id' => 5,
                'result_name' => CommentConstant::RESULT_APPROVED,
                'result_count' => $approvedCounts,
            ],
            [
                'result_id' => 1,
                'result_name' => CommentConstant::RESULT_SUCCESS,
                'result_count' => $successCounts,
            ],
            [
                'result_id' => 2,
                'result_name' => CommentConstant::RESULT_FAIL,
                'result_count' => $failCounts,
            ],
            [
                'result_id' => 3,
                'result_name' => CommentConstant::RESULT_OTHER,
                'result_count' => $otherCounts,
            ],
        ];

        return $commentCounts ? $commentCounts : [];
    }

    /**
     * @param $comments
     * @return array
     * 评论列表数据整合
     */
    public static function getComments($comments, $replyOffset)
    {
        $datas = [];
        foreach ($comments as $k => $v) {
            //关联数据
            $replyCount = isset($v['data_platform_comment_count']['reply_count']) ? $v['data_platform_comment_count']['reply_count'] : 0;
            $useCount = isset($v['data_platform_comment_count']['use_count']) ? $v['data_platform_comment_count']['use_count'] : 0;
            $addCount = isset($v['data_platform_comment_count']['add_count']) ? $v['data_platform_comment_count']['add_count'] : 0;

            $datas[$k]['platform_comment_id'] = $v['platform_comment_id'];
            $datas[$k]['content'] = $v['content'];
            $datas[$k]['score'] = CommentStrategy::getResultScore($v);
            $datas[$k]['result'] = CommentStrategy::resultIntTostr($v['result']);
            $datas[$k]['use_count'] = bcadd($v['use_count'], $v['add_count']);
            $datas[$k]['reply_count'] = $replyCount;
            $datas[$k]['create_date'] = DateUtils::formatDate($v['create_date']);
            $datas[$k]['username'] = UserFactory::fetchUserName($v['create_id']);
            $datas[$k]['replys'] = $v['replys'];
            if ($replyCount > $replyOffset) {
                $datas[$k]['replys_sign'] = 1;
            } else {
                $datas[$k]['replys_sign'] = 0;
            }
        }

        return $datas ? $datas : [];
    }

    /**
     * @param $comment
     * @return array
     * 单一评论数据处理
     */
    public static function getCommentSingle($comment)
    {
        //关联数据
        $replyCount = isset($comment['data_platform_comment_count']['reply_count']) ? $comment['data_platform_comment_count']['reply_count'] : 0;
        $useCount = isset($comment['data_platform_comment_count']['use_count']) ? $comment['data_platform_comment_count']['use_count'] : 0;
        $addCount = isset($comment['data_platform_comment_count']['add_count']) ? $comment['data_platform_comment_count']['add_count'] : 0;

        $datas['platform_comment_id'] = $comment['platform_comment_id'];
        $datas['content'] = $comment['content'];
        $datas['score'] = CommentStrategy::getResultScore($comment);
        $datas['result'] = CommentStrategy::resultIntTostr($comment['result']);
        $datas['use_count'] = bcadd($comment['use_count'], $comment['add_count']);
        $datas['reply_count'] = $replyCount;
        $datas['create_date'] = DateUtils::formatDate($comment['create_date']);
        $datas['username'] = UserFactory::fetchUserName($comment['create_id']);

        return $datas ? $datas : [];
    }

    /**
     * 评论——添加评论申请状态列表
     * result  7已批贷 , 2 被拒绝 , 4 等待审批 , 6 未申请
     * result_sign 代表星星行数 0一行 1三行
     */
    public static function getResultStatus()
    {
        $resultLists = [
            [
                'result_id' => 7,
                'result_name' => CommentConstant::RESULT_VERSION3_APPROVED_LOAN,
                'result_sign' => 0,
            ],
            [
                'result_id' => 2,
                'result_name' => CommentConstant::RESULT_VERSION3_FAIL,
                'result_sign' => 0,
            ],
            [
                'result_id' => 4,
                'result_name' => CommentConstant::RESULT_VERSION3_APPLICATION,
                'result_sign' => 0,
            ],
            [
                'result_id' => 6,
                'result_name' => CommentConstant::RESULT_VERSION3_NOT_APPLY,
                'result_sign' => 0,
            ],

        ];
        return $resultLists ? $resultLists : [];
    }

    /**
     * @return array
     * 审批时间 10分钟内  约1小时  约半天  约1天  约2天
     */
    public static function getApplyTime()
    {
        $applyTimes = [
            [
                'value' => CommentConstant::APPLY_VALUE_ONE,
                'name' => CommentConstant::APPLY_TIME_ONE,
            ],
            [
                'value' => CommentConstant::APPLY_VALUE_TWO,
                'name' => CommentConstant::APPLY_TIME_TWO,
            ],
            [
                'value' => CommentConstant::APPLY_VALUE_THREE,
                'name' => CommentConstant::APPLY_TIME_THREE,
            ],
            [
                'value' => CommentConstant::APPLY_VALUE_FOUR,
                'name' => CommentConstant::APPLY_TIME_FOUR,
            ],
            [
                'value' => CommentConstant::APPLY_VALUE_FIVE,
                'name' => CommentConstant::APPLY_TIME_FIVE,
            ],

        ];
        return $applyTimes ? $applyTimes : [];
    }

    /**
     * @param $comment
     * @param $product
     * @param $applyTime
     * @return array
     * 评论——在添加之前获取评论内容数据处理
     */
    public static function getCommentsBefore($comment, $product, $applyTime, $productId)
    {
        $datas['product_logo'] = QiniuService::getProductImgs($product['product_logo'], $productId);
        $datas['platform_product_name'] = isset($product['platform_product_name']) ? $product['platform_product_name'] : '';
        $datas['tag_name'] = isset($product['tag_name']) ? $product['tag_name'] : [];
        $comment['content'] = !empty($comment['content']) ? $comment['content'] : '';
        $comment['loan_money'] = !empty($comment['loan_money']) ? $comment['loan_money'] : '';
        //新版本2.7.5.1评论使用新字段satisfaction
        $comment['experience'] = !empty($comment['satisfaction']) ? $comment['satisfaction'] : 0;
        $result = !empty($comment['result']) ? intval($comment['result']) : 0;
        //旧版本升级新版本数据归档
        if ($result == 1 || $result == 5) {
            $comment['result'] = 7;
        } elseif ($result == 3) {
            $comment['result'] = 6;
        } else {
            $comment['result'] = $result;
        }

        $comment['product_logo'] = QiniuService::getProductImgs($product['product_logo'], $product['platform_product_id']);
        $comment['platform_product_name'] = !empty($product['platform_product_name']) ? $product['platform_product_name'] : '';
        $comment['platform_id'] = !empty($product['platform_id']) ? $product['platform_id'] : '';
        $comment['apply_time'] = $applyTime;
        $comment['loan_min'] = $product['loan_min'];
        $comment['loan_max'] = $product['loan_max'];
        //借款状态列表
        $comment['result_list'] = CommentStrategy::getResultStatus();
        //审批时间列表
        $comment['apply_time_list'] = CommentStrategy::getApplyTime();
        return $comment ? $comment : [];
    }

    /**
     * @param $data
     * @return array
     * 创建或修改评论内容  数据处理
     * 7已批贷 , 2 被拒绝 , 4 等待审批 , 6 未申请
     */
    public static function createOrUpdateComment($data)
    {
        $result = intval($data['resultId']);
        if ($result == 7 || $result == 2) {
            $data['speed'] = isset($data['speed']) ? $data['speed'] : 0;
            $data['rate'] = isset($data['rate']) ? $data['rate'] : 0;
            $data['experience'] = !empty($data['experience']) ? $data['experience'] : 0;
            $data['loan_money'] = !empty($data['loanMoney']) ? $data['loanMoney'] : 0;
        } else {
            $data['speed'] = 0;
            $data['rate'] = 0;
            $data['experience'] = !empty($data['experience']) ? $data['experience'] : 0;
            $data['loan_money'] = 0;
        }

        //result == 7 我已成功获得借款审批，金额**元
        if ($result == 7) {
            $data['content'] = !empty($data['content']) ? $data['content'] : '我已成功获得借款审批，金额' . $data['loan_money'] . '元';
        } else {
            $data['content'] = !empty($data['content']) ? $data['content'] : '这个平台不错，利率很低，而且我很快就获得了借款';
        }

        $data['platform_id'] = intval($data['platformId']);
        $data['result'] = intval($data['resultId']);

        return $data ? $data : [];
    }

    /**
     * @param $successCounts
     * @param string $failCounts
     * @param string $otherCounts
     * @param string $applicationCounts
     * @param string $approvedCounts
     * @param array $product
     * @return array
     * 7已批贷 , 2 被拒绝 , 4 等待审批 , 6 未申请
     */
    public static function fetchCommentCountsAndScore($data = [])
    {
        $commentCounts = [
            [
                'result_id' => 0,
                'result_name' => CommentConstant::RESULT_VERSION3_ALL,
                'result_count' => 0,
            ],
            [
                'result_id' => 7,
                'result_name' => CommentConstant::RESULT_VERSION3_APPROVED_LOAN,
                'result_count' => $data['approvedLoanCounts'],
            ],
            [
                'result_id' => 2,
                'result_name' => CommentConstant::RESULT_VERSION3_FAIL,
                'result_count' => $data['failCounts'],
            ],
            [
                'result_id' => 4,
                'result_name' => CommentConstant::RESULT_VERSION3_APPLICATION,
                'result_count' => $data['applicationCounts'],
            ],
            [
                'result_id' => 6,
                'result_name' => CommentConstant::RESULT_VERSION3_NOT_APPLY,
                'result_count' => $data['noApplyCounts'],
            ],
        ];

        $comment['score'] = $data['product']['satisfaction'];
        $comment['result_list'] = $commentCounts;

        return $comment ? $comment : [];
    }

    /**
     * @param $comments
     * @param $replyOffset
     * @return array
     * 最热&最新 评论列表
     */
    public static function getCommentsAndHots($comments, $replyOffset)
    {
        $datas = [];
        foreach ($comments as $k => $v) {
            //关联数据
            $replyCount = isset($v['data_platform_comment_count']['reply_count']) ? $v['data_platform_comment_count']['reply_count'] : 0;
            $useCount = isset($v['data_platform_comment_count']['use_count']) ? $v['data_platform_comment_count']['use_count'] : 0;
            $addCount = isset($v['data_platform_comment_count']['add_count']) ? $v['data_platform_comment_count']['add_count'] : 0;

            $datas[$k]['platform_comment_id'] = $v['platform_comment_id'];
            $datas[$k]['content'] = $v['content'];
            $datas[$k]['score'] = number_format($v['satisfaction'], 1);
            $datas[$k]['result'] = CommentStrategy::resultIntToString($v['result']);
            $datas[$k]['use_count'] = bcadd($v['use_count'], $v['add_count']);
            $datas[$k]['reply_count'] = $replyCount;
            $datas[$k]['create_date'] = DateUtils::formatDate($v['create_date']);
            $datas[$k]['username'] = UserFactory::fetchUserName($v['create_id']);
            $datas[$k]['replys'] = $v['replys'];
            if ($replyCount > $replyOffset) {
                $datas[$k]['replys_sign'] = 1;
            } else {
                $datas[$k]['replys_sign'] = 0;
            }
        }

        return $datas ? $datas : [];
    }

    /**
     * @param $data
     * @return string
     * 评论总数处理 大于1000 变为999+
     */
    public static function getCommentCounts($data)
    {
        $data = intval($data);
        if ($data >= 1000) {
            $data = '999+';
        }

        return $data ? $data . '' : '';
    }

    /**
     * @param $comments
     * @param $replyOffset
     * @return mixed
     * 评论回复数据处理
     */
    public static function getCommentsAndHotsDatas($comments, $replyOffset)
    {
        $datas = [];
        foreach ($comments as $key => $val) {
            //关联数据
            //$replyCount = isset($val['data_platform_comment_count']['reply_count']) ? $val['data_platform_comment_count']['reply_count'] : 0;
            $useCount = isset($val['data_platform_comment_count']['use_count']) ? $val['data_platform_comment_count']['use_count'] : 0;
            $addCount = isset($val['data_platform_comment_count']['add_count']) ? $val['data_platform_comment_count']['add_count'] : 0;
            $replyCount = ReplyFactory::fetchReplyCountById($val['platform_comment_id']);

            $datas[$key]['platform_comment_id'] = $val['platform_comment_id'];
            $datas[$key]['content'] = $val['content'];
            $datas[$key]['score'] = number_format($val['satisfaction'], 1);
            $datas[$key]['result'] = CommentStrategy::resultIntToString($val['result']);
            $datas[$key]['use_count'] = DateUtils::formatMathToThous(bcadd($val['use_count'], $val['add_count']));
            $datas[$key]['reply_count'] = $replyCount;
            //用于处理99+点赞总数
            $datas[$key]['praise_count'] = round(bcadd($val['use_count'], $val['add_count']));
            //用于处理99+回复总数
            $datas[$key]['answer_count'] = round($replyCount);
            $datas[$key]['create_date'] = DateUtils::formatDateToMdhi($val['create_date']);
            $username = UserFactory::fetchUserNameAndMobile($val['create_id']);
            $user = UserStrategy::replaceUsernameSd($username);
            $datas[$key]['username'] = $user['username'];
            $datas[$key]['user_photo'] = UserFactory::fetchUserPhotoById($val['create_id']);
            if ($val['replys']) {
                foreach ($val['replys'] as $k => $v) {
                    $datas[$key]['replys'][$k]['id'] = $v['id'];
                    $datas[$key]['replys'][$k]['username'] = $v['username'];
                    $datas[$key]['replys'][$k]['content'] = $v['content'];
                    $datas[$key]['replys'][$k]['parent_username'] = isset($v['parent']['username']) ? $v['parent']['username'] : '';
                }
            } else {
                $datas[$key]['replys'] = [];
            }

            if ($replyCount >= $replyOffset) {
                $datas[$key]['replys_sign'] = 1;
            } else {
                $datas[$key]['replys_sign'] = 0;
            }
        }
        return $datas;
    }

    /**
     * @param array $comment
     * @return array
     * 多级回复  获取楼主评论内容
     */
    public static function getCommentLandlord($comment = [])
    {
        //$replyCount = isset($comment['data_platform_comment_count']['reply_count']) ? $comment['data_platform_comment_count']['reply_count'] : 0;
        $useCount = isset($comment['data_platform_comment_count']['use_count']) ? $comment['data_platform_comment_count']['use_count'] : 0;
        $addCount = isset($comment['data_platform_comment_count']['add_count']) ? $comment['data_platform_comment_count']['add_count'] : 0;
        //求回复总个数
        $replyCount = ReplyFactory::fetchReplyCountById($comment['platform_comment_id']);

        $datas['platform_comment_id'] = $comment['platform_comment_id'];
        $datas['content'] = $comment['content'];
        $datas['score'] = number_format($comment['satisfaction'], 1);
        $datas['result'] = CommentStrategy::resultIntToString($comment['result']);
        $datas['use_count'] = DateUtils::formatMathToThous(bcadd($comment['use_count'], $comment['add_count']));
        $datas['reply_count'] = DateUtils::formatMathToThous($replyCount);
        $datas['create_date'] = DateUtils::formatDateToMdhi($comment['create_date']);
        $username = UserFactory::fetchUserNameAndMobile($comment['create_id']);
        $username = UserStrategy::replaceUsernameSd($username);
        $datas['username'] = $username['username'];
        $datas['user_photo'] = UserFactory::fetchUserPhotoById($comment['create_id']);

        return $datas ? $datas : [];
    }

    /**
     * 产品详情对应评论数据整理
     * @param array $params
     * @return array
     */
    public static function getDetailComments($params = [])
    {
        $datas = [];
        foreach ($params as $key => $val) {
            $datas[$key]['platform_comment_id'] = $val['platform_comment_id'];
            $datas[$key]['content'] = $val['content'];
            $datas[$key]['score'] = number_format($val['satisfaction'], 1);
            $datas[$key]['result'] = CommentStrategy::resultIntToString($val['result']);
            $datas[$key]['use_count'] = DateUtils::formatMathToThous(bcadd($val['use_count'], $val['add_count']));
            $datas[$key]['create_date'] = DateUtils::formatDateToMdhi($val['create_date']);
            $username = UserFactory::fetchUserNameAndMobile($val['create_id']);
            $user = UserStrategy::replaceUsernameSd($username);
            $datas[$key]['username'] = $user['username'];
            $datas[$key]['user_photo'] = UserFactory::fetchUserPhotoById($val['create_id']);
        }

        return $datas ? $datas : [];
    }
}



