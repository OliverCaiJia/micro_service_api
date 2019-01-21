<?php

namespace App\Models\Factory;

use App\Helpers\Utils;
use App\Models\AbsModelFactory;
use App\Models\Orm\UserAlipay;
use App\Models\Orm\UserBanks;
use App\Models\Orm\UserCertify;
use App\Models\Orm\UserIdentity;
use App\Models\Orm\UserProfile;
use App\Strategies\BankStrategy;
use App\Strategies\UserCertifyStrategy;
use App\Strategies\SexStrategy;
use App\Strategies\UserIdentityStrategy;
use App\Strategies\UserinfoStrategy;
use App\Strategies\UserProfileStrategy;

/**
 * Class UserinfoFactory
 * @package App\Models\Factory
 * 用户信息工厂类
 */
class UserinfoFactory extends AbsModelFactory
{
    /**
     * @param $userId
     * @return array
     * 基础信息 —— 查询用户基础信息
     */
    public static function fetchBasicinfo($userId)
    {
        $basicObj = UserProfile::select(['user_id', 'real_name', 'identity_card', 'sex', 'age'])
            ->where(['user_id' => $userId])
            ->first();
        return $basicObj ? $basicObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 基础信息 —— 查询信用卡&学信网账号
     */
    public static function fetchXuexinAndCredit($userId)
    {
        $certifyArr = UserCertify::select(['xuexin_website', 'credit'])
            ->where(['user_id' => $userId])
            ->first();

        return $certifyArr ? $certifyArr->toArray() : [];
    }

    /**
     * @param $userId
     * @param $indent
     * @return array
     * 基础信息 —— 根据身份 查询信用卡&学信网账号
     */
    public static function fetchXuexinAndCreditByIndent($userId, $indent)
    {
        if ($indent == 1) {
            //大学生
            $certifyArr = UserCertify::select(['xuexin_website'])
                ->where(['user_id' => $userId])
                ->first();
        } else {
            $certifyArr = UserCertify::select(['credit'])
                ->where(['user_id' => $userId])
                ->first();
        }

        return $certifyArr ? $certifyArr->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 用户基础信息
     */
    public static function fetchUserProfile($userId)
    {
        $profileObj = UserProfile::select(['real_name', 'identity_card', 'address', 'address_type', 'marriage',
            'emergency_contact', 'emergency_contact_mobile', 'emergency_contact_relation'])
            ->where(['user_id' => $userId])
            ->first();

        return $profileObj ? $profileObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 身份为学生的的审核资料
     */
    public static function fetchUserCertifyToStudent($userId)
    {
        $certifyObj = UserCertify::select(['xuexin_website', 'zhima_certify', 'people_bank_report',
            'taobao_certify', 'jingdong_certify', 'credit_money', 'provident_fund_money'])
            ->where(['user_id' => $userId])
            ->first();

        return $certifyObj ? $certifyObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 身份除了学生的用户审核资料
     */
    public static function fetchUserCertify($userId)
    {
        $certifyObj = UserCertify::select(['credit', 'zhima_certify', 'people_bank_report',
            'taobao_certify', 'jingdong_certify', 'credit_money', 'provident_fund_money'])
            ->where(['user_id' => $userId])
            ->first();

        return $certifyObj ? $certifyObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 学生 —— 职业信息
     */
    public static function fetchStudentIdentity($userId)
    {
        $identityObj = UserIdentity::select(['school_name', 'studies', 'graduate_long_year'])
            ->where(['user_id' => $userId])
            ->first();

        return $identityObj ? $identityObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 上班族 —— 职业信息
     */
    public static function fetchWorkerIdentity($userId)
    {
        $identityObj = UserIdentity::select(['certificate', 'company_name', 'company_nature',
            'working_years', 'month_income', 'is_company_email', 'wage_water_proof'])
            ->where(['user_id' => $userId])
            ->first();

        return $identityObj ? $identityObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 生意人 —— 职业信息
     */
    public static function fetchBusinessmanIdentity($userId)
    {
        $identityObj = UserIdentity::select(['certificate', 'company_name', 'company_nature',
            'manage_time', 'month_income', 'business_license', 'is_bill'])
            ->where(['user_id' => $userId])
            ->first();

        return $identityObj ? $identityObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return array
     * 自由职业者 —— 职业信息
     */
    public static function fetchFreelancerIdentity($userId)
    {
        $identityObj = UserIdentity::select(['income_source'])
            ->where(['user_id' => $userId])
            ->first();

        return $identityObj ? $identityObj->toArray() : [];
    }

    /**
     * @param $userId
     * @return mixed
     * 基础信息 —— 进度条
     */
    public static function fetchProgress($userId)
    {
        //根据 userId 查询 Account
        $userAccount = BankFactory::fetchBanksArray($userId);
        //银行卡信息 Name
        $bankArr = BankFactory::fetchBankNameByBankId($userAccount);
        //数据处理 得到 Account & Name
        $userBanksArr = BankStrategy::getAccountAndName($userAccount, $bankArr);

        //身份
        $indent = UserFactory::fetchUserIndent($userId);
        //基础信息与审核资料信息
        $profileArr = UserinfoFactory::fetchUserProfile($userId);
        if ($indent == 1) {
            $certifyArr = UserinfoFactory::fetchUserCertifyToStudent($userId);
        } else {
            $certifyArr = UserinfoFactory::fetchUserCertify($userId);
        }
        //支付宝
        $alipayArr = BankFactory::fetchAlipayArray($userId);

        switch ($indent) {
            case 1:
                //大学生
                $identityArr = UserinfoFactory::fetchStudentIdentity($userId);
                return UserinfoStrategy::mergeArrayProgress($profileArr, $certifyArr, $identityArr, $userBanksArr, $alipayArr, $indent);
                break;
            case 2:
                //工薪族
                $identityArr = UserinfoFactory::fetchWorkerIdentity($userId);
                return UserinfoStrategy::mergeArrayProgress($profileArr, $certifyArr, $identityArr, $userBanksArr, $alipayArr, $indent);
                break;
            case 3:
                //企业主
                $identityArr = UserinfoFactory::fetchBusinessmanIdentity($userId);
                return UserinfoStrategy::mergeArrayProgress($profileArr, $certifyArr, $identityArr, $userBanksArr, $alipayArr, $indent);
                break;
            case 4:
                $identityArr = UserinfoFactory::fetchFreelancerIdentity($userId);
                return UserinfoStrategy::mergeArrayProgress($profileArr, $certifyArr, $identityArr, $userBanksArr, $alipayArr, $indent);
                break;

        }
    }

    /**
     * @param $progCounts
     * @return array
     * 进度条计算返回值
     */
    public static function fetchProgressArray($progCounts)
    {
        $progArr['progCounts'] = !empty($progCounts['userInfoCounts']) ? $progCounts['userInfoCounts'] : 0;
        $progArr['progress'] = !empty($progCounts['progress']) ? $progCounts['progress'] : 0;

        return $progArr ? $progArr : [];
    }

    /**
     * @param $data
     * @param $userId
     * @return bool
     * 基础信息 —— 修改基础信息
     */
    public static function updateProfile($data, $userId)
    {
        $profileObj = UserProfile::where(['user_id' => $userId])->first();
        if (empty($profileObj)) {
            $profileObj = new UserProfile();
            //只创建一次不修改
            $profileObj->create_at = date('Y-m-d H:i:s', time());
            $profileObj->create_id = $userId;
            $profileObj->create_ip = Utils::ipAddress();
            $profileObj->outApplyNo = $userId . date('Ymd', time()) . time() . rand(1000, 9999);
        }

        $profileObj->user_id = $userId;
        $profileObj->real_name = $data['realName'];
        $profileObj->identity_card = $data['identityCard'];
        $profileObj->sex = SexStrategy::strToInt($data['sex']);
        $profileObj->age = intval($data['age']);
        $profileObj->update_at = date('Y-m-d H:i:s', time());
        $profileObj->update_id = $userId;
        $profileObj->update_ip = Utils::ipAddress();


        return $profileObj->save();
    }

    /**
     * @param $data
     * @param $userId
     * @return mixed
     * 基础信息 —— 修改、添加学信网/信用卡信息
     */
    public static function updateCertify($data, $userId)
    {
        //审核资料
        $certifyObj = UserCertify::where(['user_id' => $userId])->first();
        if (empty($certifyObj)) {
            $certifyObj = new UserCertify();
            //只创建一次不修改
            $certifyObj->create_at = date('Y-m-d H:i:s', time());
            $certifyObj->create_id = $userId;
            $certifyObj->create_ip = Utils::ipAddress();
        }

        $certifyObj->user_id = $userId;
        $credit = empty($certifyObj->credit) ? '' : $certifyObj->credit;
        $certifyObj->credit = !empty($data['credit']) ? UserCertifyStrategy::creditstrToInt($data['credit']) : $credit;
        $xuexinWebsite = empty($certifyObj->xuexin_website) ? '' : $certifyObj->xuexin_website;
        $certifyObj->xuexin_website = !empty($data['xuexinWebsite']) ?
            UserCertifyStrategy::twostrToInt($data['xuexinWebsite']) : $xuexinWebsite;
        $certifyObj->update_at = date('Y-m-d H:i:s', time());
        $certifyObj->update_id = $userId;
        $certifyObj->update_ip = Utils::ipAddress();

        return $certifyObj->save();
    }

    /**
     * @param $data
     * @param $userId
     * @return bool
     * @card_use 使用状态【0信用资料，1认证银行】
     * 基础信息 —— 修改、添加银行卡号
     */
    public static function updateUserBanks($data, $userId)
    {
        $userBanksObj = UserBanks::where(['user_id' => $userId, 'status' => 0, 'card_use' => 0])->first();
        if (empty($userBanksObj)) {
            $userBanksObj = new UserBanks();
            //只创建一次不修改
            $userBanksObj->created_at = date('Y-m-d H:i:s', time());
            $userBanksObj->created_ip = Utils::ipAddress();
        }

        $userBanksObj->user_id = $userId;
        $userBanksObj->card_use = 0;
        $userBanksObj->bank_id = !empty($data['bankId']) ? $data['bankId'] : 1;
        $userBanksObj->account = Utils::removeSpaces($data['account']);
        $userBanksObj->updated_at = date('Y-m-d H:i:s', time());
        $userBanksObj->updated_ip = Utils::ipAddress();


        return $userBanksObj->save();
    }

    /**
     * @param $data
     * @param $userId
     * @return mixed
     * 基础信息 —— 修改、添加支付宝账号
     */
    public static function updateUserAlipay($data, $userId)
    {
        $userAlipayObj = UserAlipay::where(['user_id' => $userId])->first();

        if (empty($userAlipayObj)) {
            $userAlipayObj = new UserAlipay();
            //只创建不修改
            $userAlipayObj->created_at = date('Y-m-d H:i:s', time());
            $userAlipayObj->created_id = $userId;
            $userAlipayObj->created_ip = Utils::ipAddress();
        }

        $userAlipayObj->user_id = $userId;
        $userAlipayObj->alipay = $data['alipay'];
        $userAlipayObj->updated_at = date('Y-m-d H:i:s', time());
        $userAlipayObj->updated_id = $userId;
        $userAlipayObj->updated_ip = Utils::ipAddress();


        return $userAlipayObj->save();
    }

    /**
     * @param $userId
     * @param $indent
     * @param $userProfileArr
     * 信用信息 —— 查询
     */
    public static function fetchIdentityinfo($userId, $indent, $userProfileArr)
    {
        $datas = [];
        switch ($indent) {
            case 1: //学生
                $identityArr = UserinfoFactory::fetchStudentIdentity($userId);
                //数据处理
                $datas = UserinfoStrategy::getStudentIdentityinfo($identityArr, $userProfileArr);
                break;
            case 2: //上班族
                $identityArr = UserinfoFactory::fetchWorkerIdentity($userId);
                //数据处理
                $datas = UserinfoStrategy::getWokerIdentityinfo($identityArr, $userProfileArr);
                break;
            case 3://企业主
                $identityArr = UserinfoFactory::fetchBusinessmanIdentity($userId);
                //数据处理
                $datas = UserinfoStrategy::getBusinessmanIdentityinfo($identityArr, $userProfileArr);
                break;
            case 4://其他
                $identityArr = UserinfoFactory::fetchFreelancerIdentity($userId);
                //数据处理
                $datas = UserinfoStrategy::getFreelancerIdentityinfo($identityArr, $userProfileArr);
                break;
        }
        return $datas;
    }

    /**
     * @param $userId
     * @param $data
     * 信用信息 —— 修改信用信息中的个人信息
     */
    public static function updateProfilesOfIdentity($userId, $data)
    {
        $profileObj = UserProfile::where(['user_id' => $userId])->first();
        if (empty($profileObj)) {
            $profileObj = new UserProfile();
            //只创建一次不修改
            $profileObj->create_at = date('Y-m-d H:i:s', time());
            $profileObj->create_id = $userId;
            $profileObj->create_ip = Utils::ipAddress();
            $profileObj->outApplyNo = $userId . date('Ymd', time()) . time() . rand(1000, 9999);
        }

        $profileObj->user_id = $userId;
        $profileObj->address = trim($data['address']);
        $profileObj->address_type = UserProfileStrategy::addstrToInt($data['addressType']);
        $profileObj->marriage = UserProfileStrategy::marstrToInt($data['marriage']);
        $profileObj->emergency_contact = trim($data['emergencyContact']);
        $profileObj->emergency_contact_mobile = trim($data['emergencyContactMobile']);
        $profileObj->emergency_contact_relation = UserProfileStrategy::emestrToInt($data['emergencyContactRelation']);
        $profileObj->update_at = date('Y-m-d H:i:s', time());
        $profileObj->update_id = $userId;
        $profileObj->update_ip = Utils::ipAddress();


        return $profileObj->save();
    }

    /**
     * @param $userId
     * @param $data
     * @return bool
     * 信用信息 —— 创建或修改职业信息
     */
    public static function updateIdentityById($userId, $indent, $data)
    {
        $identityObj = UserIdentity::where(['user_id' => $userId])->first();
        if (empty($identityObj)) {
            $identityObj = new UserIdentity();
            //只创建一次不修改
            $identityObj->create_at = date('Y-m-d H:i:s', time());
            $identityObj->create_id = $userId;
            $identityObj->create_ip = Utils::ipAddress();
        }

        //$user 存储用户信息
        $identityObj->user_id = $userId;
        $identityObj->identity = intval($indent);
        $identityObj->update_at = date('Y-m-d H:i:s', time());
        $identityObj->update_id = $userId;
        $identityObj->update_ip = Utils::ipAddress();
        switch ($indent) {
            case 1:     //大学生
                $identityObj->school_name = trim($data['schoolName']);
                $identityObj->studies = UserIdentityStrategy::stustrToInt($data['studies']);
                $identityObj->graduate_long_year = UserIdentityStrategy::grastrToInt($data['graduateLongYear']);
                break;
            case 2: //  上班族
                $identityObj->certificate = UserIdentityStrategy::cerstrToInt($data['certificate']);
                $identityObj->company_name = trim($data['companyName']);
                $identityObj->working_years = !empty($data['workingYears']) ? UserIdentityStrategy::workstrToInt($data['workingYears']) : $identityObj->working_years;
                $identityObj->is_company_email = UserIdentityStrategy::emailstrToInt($data['isCompanyEmail']);
                $identityObj->month_income = UserIdentityStrategy::monstrToInt($data['monthIncome']);
                $identityObj->wage_water_proof = UserIdentityStrategy::wagestrToInt($data['wageWaterProof']);
                $identityObj->company_nature = UserIdentityStrategy::comstrToInt($data['companyNature']);
                break;
            case 3:     //企业主
                $identityObj->certificate = UserIdentityStrategy::cerstrToInt($data['certificate']);
                $identityObj->company_name = trim($data['companyName']);
                $identityObj->company_nature = UserIdentityStrategy::comstrToInt($data['companyNature']);
                $identityObj->manage_time = UserIdentityStrategy::manstrToInt($data['manageTime']);
                $identityObj->business_license = UserIdentityStrategy::busistrToInt($data['businessLicense']);
                $identityObj->month_income = UserIdentityStrategy::monstrToInt($data['monthIncome']);
                $identityObj->is_bill = UserIdentityStrategy::billstrToInt($data['isBill']);
                break;
            case 4:     //其他
                $identityObj->income_source = UserIdentityStrategy::incomestrToInt($data['incomeSource']);
                break;
        }
        return $identityObj->save();
    }

    /**
     * @param $data
     * @param $userId
     * @return mixed
     * 审核资料 —— 创建&修改 用户审核资料
     */
    public static function updateCertityinfo($data, $userId)
    {
        $certifyObj = UserCertify::where(['user_id' => $userId])->first();
        if (empty($certifyObj)) {
            $certifyObj = new UserCertify();
            //第一次创建不修改
            $certifyObj->create_at = date('Y-m-d H:i:s', time());
            $certifyObj->create_id = $userId;
            $certifyObj->create_ip = Utils::ipAddress();
        }

        //修改
        $certifyObj->user_id = $userId;
        $certifyObj->zhima_certify = UserCertifyStrategy::zhistrToInt($data['zhimaCertify']);
        $certifyObj->taobao_certify = UserCertifyStrategy::twostrToInt($data['taobaoCertify']);
        $certifyObj->jingdong_certify = UserCertifyStrategy::twostrToInt($data['jingdongCertify']);
        $certifyObj->people_bank_report = UserCertifyStrategy::twostrToInt($data['peopleBankReport']);
        $certifyObj->credit_money = UserCertifyStrategy::crestrToInt($data['creditMoney']);
        $certifyObj->provident_fund_money = UserCertifyStrategy::crestrToInt($data['providentFundMoney']);
        $certifyObj->update_at = date('Y-m-d H:i:s', time());
        $certifyObj->update_id = intval($userId);
        $certifyObj->update_ip = Utils::ipAddress();

        return $certifyObj->save();
    }

    /**
     * @param $userId
     * @return int
     * @is_identity 是否选身份【0没选，1已选】
     * 是否选择身份
     */
    public static function fetchIsIdentityById($userId)
    {
        $isIdentity = UserIdentity::select(['is_identity'])
            ->where(['user_id' => $userId])
            ->first();

        return $isIdentity ? $isIdentity->is_identity : 0;
    }

    /**
     * @param $userId
     * @return int|mixed
     * 根据用户id获取sd_profile_id
     */
    public static function fetchProfileIdByUserId($userId)
    {
        $isIdentity = UserProfile::select(['sd_profile_id'])
            ->where(['user_id' => $userId])
            ->first();

        return $isIdentity ? $isIdentity->sd_profile_id : 0;
    }

    /**
     * 根据用户id修改身份证信息
     * @param array $params
     * @return bool
     */
    public static function updateProfileById($params = [])
    {
        $profile = UserProfile::where(['sd_profile_id' => $params['profile_id']])->first();
        if (!$profile) {
            $profile = new UserProfile();
            $profile->create_at = date('Y-m-d H:i:s', time());
            $profile->create_id = $params['userId'];
            $profile->create_ip = Utils::ipAddress();
        }

        $profile->user_id = $params['userId'];
        $profile->real_name = $params['idcard_name'];
        $profile->identity_card = $params['idcard_number'];
        $profile->sex = $params['sex'];
        $profile->update_at = date('Y-m-d H:i:s', time());
        $profile->update_id = $params['userId'];
        $profile->update_ip = Utils::ipAddress();

        return $profile->save();
    }
}