<?php
namespace App\Strategies;

/**
 * Class UserinfoStrategy
 * @package App\Strategies
 * 用户信息策略层
 */
class UserinfoStrategy extends AppStrategy
{
    /**
     * @param $basicArr
     * @param $userBanksArr
     * @param $alipay
     * @param $mobile
     * @param $certifyArr
     * 基础信息 —— 查询用户基础信息
     */
    public static function getBasicinfo($basicArr, $userBanksArr, $alipay, $mobile, $certifyArr, $progCounts)
    {
        $userData['mobile'] = $mobile;
        $userData['real_name'] = !empty($basicArr['real_name']) ? $basicArr['real_name'] : '';
        $userData['identity_card'] = !empty($basicArr['identity_card']) ? $basicArr['identity_card'] : '';
        $userData['sex'] = !empty($basicArr['identity_card']) ? SexStrategy::intToStr($basicArr['sex']) : '';
        $userData['age'] = !empty($basicArr['age']) ? $basicArr['age'] : '';
        $userData['account'] = !empty($userBanksArr['account']) ? $userBanksArr['account'] : '';
        $userData['name'] = !empty($userBanksArr['name']) ? $userBanksArr['name'] : '';
        $userData['bank_id'] = !empty($userBanksArr['id']) ? $userBanksArr['id'] : '';
        $userData['alipay'] = !empty($alipay) ? $alipay : '';
        $credit = !empty($certifyArr['credit']) ? $certifyArr['credit'] : '';
        $xuexin_website = !empty($certifyArr['xuexin_website']) ? $certifyArr['xuexin_website'] : '';
        $userData['credit'] = UserCertifyStrategy::creditintToStr($credit);
        $userData['xuexin_website'] = UserCertifyStrategy::twointToStr($xuexin_website);
        $userData['progCounts'] = !empty($progCounts['userInfoCounts']) ? $progCounts['userInfoCounts'] : 0;
        $userData['progress'] = !empty($progCounts['progress']) ? $progCounts['progress'] : '';

        return $userData;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @param array $array3
     * @param array $array4
     * @param array $array5
     * @param string $indent
     * @return mixed
     * 进度条计算合并数组  求进度值 0.23
     */
    public static function mergeArrayProgress($array1 = array(), $array2 = array(), $array3 = array(), $array4 = array(), $array5 = array(), $indent = '')
    {
        $array1 = !empty($array1) ? $array1 : array();
        $array2 = !empty($array2) ? $array2 : array();
        $array3 = !empty($array3) ? $array3 : array();
        $array4 = !empty($array4) ? $array4 : array();
        $array5 = !empty($array5) ? $array5 : array();
        $datas = array_merge($array1, $array2, $array3, $array4, $array5);
        $datas = array_filter($datas);
        $userInfoCounts = count($datas);

        if ($indent == 1) {
            $progress = bcmul(bcdiv($userInfoCounts, 21, 2), 100);
        } elseif ($indent == 2) {
            $progress = bcmul(bcdiv($userInfoCounts, 25, 2), 100);
        } elseif ($indent == 3) {
            $progress = bcmul(bcdiv($userInfoCounts, 25, 2), 100);
        } elseif ($indent == 4) {
            $progress = bcmul(bcdiv($userInfoCounts, 19, 2), 100);
        } else {
            $progress = 0;
        }
        $res['userInfoCounts'] = $userInfoCounts;
        $res['progress'] = $progress;
        return $res;
    }

    /**
     * @param $profileArr
     * 信用信息 —— 数据处理
     */
    public static function getProfileinfo($profileArr)
    {
        $datas['address'] = !empty($profileArr['address']) ? $profileArr['address'] : '';
        $datas['address_type'] = UserProfileStrategy::addintToStr($profileArr['address_type']);
        $datas['marriage'] = UserProfileStrategy::marintToStr($profileArr['marriage']);
        $datas['emergency_contact'] = $profileArr['emergency_contact'];
        $datas['emergency_contact_mobile'] = !empty($profileArr['emergency_contact_mobile']) ? $profileArr['emergency_contact_mobile'] : '';
        $datas['emergency_contact_relation'] = UserProfileStrategy::emeintToStr($profileArr['emergency_contact_relation']);

        return $datas ? $datas : [];
    }

    /**
     * @param $identityArr
     * @return mixed
     * 信用信息 —— 学生身份数据处理
     */
    public static function getStudentIdentityinfo($identityArr, $userProfileArr)
    {
        $userProfileArr['school_name'] = !empty($identityArr['school_name']) ? $identityArr['school_name'] : '';
        $userProfileArr['studies'] = UserIdentityStrategy::stuintToStr($identityArr['studies']);
        $userProfileArr['graduate_long_year'] = UserIdentityStrategy::graintToStr($identityArr['graduate_long_year']);

        return $userProfileArr;
    }

    /**
     * @param $identityArr
     * @param $userProfileArr
     * 信用信息 —— 工薪族身份数据处理
     */
    public static function getWokerIdentityinfo($identityArr, $userProfileArr)
    {
        $userProfileArr['certificate'] = UserIdentityStrategy::cerintToStr($identityArr['certificate']);
        $userProfileArr['company_name'] = !empty($identityArr['company_name']) ? $identityArr['company_name'] : '';
        $userProfileArr['company_nature'] = UserIdentityStrategy::comintToStr($identityArr['company_nature']);
        $userProfileArr['working_years'] = UserIdentityStrategy::workintToStr($identityArr['working_years']);
        $userProfileArr['is_company_email'] = UserIdentityStrategy::emailintToStr($identityArr['is_company_email']);
        $userProfileArr['month_income'] = UserIdentityStrategy::monintToStr($identityArr['month_income']);
        $userProfileArr['wage_water_proof'] = UserIdentityStrategy::wageintToStr($identityArr['wage_water_proof']);

        return $userProfileArr;
    }

    /**
     * @param $identityArr
     * @param $userProfileArr
     * @return mixed
     * 信用信息 —— 老板身份数据处理
     */
    public static function getBusinessmanIdentityinfo($identityArr, $userProfileArr)
    {
        $userProfileArr['certificate'] = UserIdentityStrategy::cerintToStr($identityArr['certificate']);
        $userProfileArr['company_name'] = $identityArr['company_name'];
        $userProfileArr['company_nature'] = UserIdentityStrategy::comintToStr($identityArr['company_nature']);
        $userProfileArr['manage_time'] = UserIdentityStrategy::manintToStr($identityArr['manage_time']);
        $userProfileArr['business_license'] = UserIdentityStrategy::busiintToStr($identityArr['business_license']);
        $userProfileArr['month_income'] = UserIdentityStrategy::monintToStr($identityArr['month_income']);
        $userProfileArr['is_bill'] = UserIdentityStrategy::billintToStr($identityArr['is_bill']);

        return $userProfileArr;
    }

    /**
     * @param $identityArr
     * @param $userProfileArr
     * 信用信息 —— 自由职业者身份数据处理
     */
    public static function getFreelancerIdentityinfo($identityArr, $userProfileArr)
    {
        $userProfileArr['income_source'] = UserIdentityStrategy::incomeintToStr($identityArr['income_source']);

        return $userProfileArr;
    }

    /**
     * @param $cerArr
     * 审核资料 —— 查询用户审核资料数据处理
     */
    public static function getCertifyinfo($cerArr)
    {
        $certifyArr['zhima_certify'] = UserCertifyStrategy::zhiintToStr($cerArr['zhima_certify']);
        $certifyArr['taobao_certify'] = UserCertifyStrategy::twointToStr($cerArr['taobao_certify']);
        $certifyArr['jingdong_certify'] = UserCertifyStrategy::twointToStr($cerArr['jingdong_certify']);
        $certifyArr['people_bank_report'] = UserCertifyStrategy::twointToStr($cerArr['people_bank_report']);
        $certifyArr['credit_money'] = UserCertifyStrategy::creintToStr($cerArr['credit_money']);
        $certifyArr['provident_fund_money'] = UserCertifyStrategy::creintToStr($cerArr['provident_fund_money']);

        return $certifyArr ? $certifyArr : [];
    }

    /**
     * @param array $array1
     * @param array $array2
     * @param array $array3
     * @param array $array4
     * @param array $array5
     * @return mixed
     * 合并数组处理
     */
    public static function mergeArray($array1 = [], $array2 = [], $array3 = [], $array4 = [], $array5 = [])
    {
        $array1 = !empty($array1) ? $array1 : array();
        $array2 = !empty($array2) ? $array2 : array();
        $array3 = !empty($array3) ? $array3 : array();
        $array4 = !empty($array4) ? $array4 : array();
        $array5 = !empty($array5) ? $array5 : array();
        $datas = array_merge($array1, $array2, $array3, $array4, $array5);
        $datas = array_filter($datas);
        $userInfoCounts = count($datas);
        return $userInfoCounts;
    }

}