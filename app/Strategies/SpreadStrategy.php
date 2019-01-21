<?php

namespace App\Strategies;

use App\Constants\SpreadConstant;
use App\Models\Factory\UserSpreadFactory;
use App\Strategies\AppStrategy;
use App\Models\Factory\UserIdentityFactory;
use App\Helpers\Utils;
use App\Constants\SpreadNidConstant;

/**
 * 策略
 *
 * @package App\Strategies
 */
class SpreadStrategy extends AppStrategy
{
    /**
     * 获取推送产品限制
     *
     * @param $typeNid
     * @param $mobile
     * @return bool
     */
    public static function getPushProductLimit($typeNid, $mobile)
    {
        $type_id = UserSpreadFactory::getTypeId($typeNid);
        //进行时间限制
        $createAt = UserSpreadFactory::getSpreadLogInfo($mobile, $type_id);
        if (!empty($createAt)) {
            $now = time();
            $createTime = strtotime($createAt) + (24 * 60 * 60);
            if ($now < $createTime) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取推广产品
     *
     * @param $mobile
     * @return array
     */
    public static function getRePushProduct($mobile)
    {
        $partner = [];
        $zhudaiTypeId = UserSpreadFactory::getTypeId(UserSpreadFactory::SPREAD_ZHUDAIWANG_NID);
        $zhudai = UserSpreadFactory::getSpreadLogInfo($mobile, $zhudaiTypeId);
        $xinyiTypeId = UserSpreadFactory::getTypeId(UserSpreadFactory::SPREAD_XINYIDAI_NID);
        $xinyi = UserSpreadFactory::getSpreadLogInfo($mobile, $xinyiTypeId);
        $paipaiTypeId = UserSpreadFactory::getTypeId(UserSpreadFactory::SPREAD_PAIPAIDAI_NID);
        $paipai = UserSpreadFactory::getSpreadLogInfo($mobile, $paipaiTypeId);
        $oxygendaiTypeId = UserSpreadFactory::getTypeId(UserSpreadFactory::SPREAD_OXYGENDAI_NID);
        $oxygendai = UserSpreadFactory::getSpreadLogInfo($mobile, $oxygendaiTypeId);
        $xiaoxiaoTypeId = UserSpreadFactory::getTypeId(UserSpreadFactory::SPREAD_XIAOXIAO_NID);
        $xiaoxiao = UserSpreadFactory::getSpreadLogInfo($mobile, $xiaoxiaoTypeId);
        if (!empty($zhudai)) {
            //助贷网
            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_ZHUDAIWANG_NID);
        }

        if (!empty($xinyi)) {
            //新一贷
            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_XINYIDAI_NID);
        }
        if (!empty($paipai)) {
            // 拍拍贷
            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_PAIPAIDAI_NID);
        }
        if (!empty($oxygendai)) {
            // 氧气贷
            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_OXYGENDAI_NID);
        }
        if (!empty($xiaoxiao)) {
            // 小小金融
            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_XIAOXIAO_NID);
        }

        return $partner;
    }

    /**
     * 获取推送产品
     *
     * @param $age
     * @return array
     */
    public static function getPushProduct($age)
    {
        $partner = [];
        if ($age >= 23 && $age <= 55) {
            if ($age >= 25 && $age <= 55) {
                //助贷网
                $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_ZHUDAIWANG_NID);
            }
            //小小金融
//            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_XIAOXIAO_NID);
            //新一贷
            $partner[] = UserSpreadFactory::getSpreadType(UserSpreadFactory::SPREAD_XINYIDAI_NID);
        }

        return $partner;
    }

    /**
     * 获取工资发放方式
     * @param $type
     * @return int
     */
    public static function getSalaryExtend($type = '')
    {
        if ($type == '001') {
            return 1;
        } elseif ($type == '002') {
            return 2;
        }
    }

    /**
     * 获取车产情况
     * @param $type
     * @return int
     */
    public static function getCarType($type = '')
    {
        if ($type == '000') {
            //无车
            return 2;
        } elseif ($type == '001') {
            // 有车贷
            return 3;
        } elseif ($type == '002') {
            // 无车贷
            return 4;
        }
    }

    /**
     * 获取职业
     */
    public static function getworkType($type = '')
    {
        if ($type == '001' || $type == '002') {
            //上班族
            return 4;
        } elseif ($type == '003') {
            // 私营业主
            return 3;
        }
    }

    /**
     * 拼接图片链接
     * @param array $partners
     * @return array
     */
    public static function getPartners($partners = [])
    {
        $prefix = config('sudai.qiniu.baseurl');
        foreach ($partners as $index => $partner) {
            $partners[$index]['logo'] = $prefix . $partner['logo'];
        }

        return $partners;
    }

    public static function getInfo($spread = null)
    {
        $result = [];
        if (isset($spread)) {
            $aptitude = [];
            if (in_array($spread->car_info, ['001', '002'])) {
                $aptitude[] = 1;
            }

            if (in_array($spread->house_info, ['001', '002'])) {
                $aptitude[] = 2;
            }

            if ($spread->has_insurance == 1) {
                $aptitude[] = 3;
            }

            if ($spread->has_creditcard == 1) {
                $aptitude[] = 4;
            }

            if ($spread->social_security == 1) {
                $aptitude[] = 5;
            }

            $result['aptitude'] = $aptitude;
            $result['money'] = $spread->money;
        }

        return $result;
    }

    /**
     * 设置城市编码
     *
     * @return array
     */
    public static function setCityCode()
    {

        return [
            '北京' => '110000',
            '东莞' => '441900',
            '南京' => '320100',
            '广州' => '440100',
            '深圳' => '440300',
            '武汉' => '420100',
            '苏州' => '320500',
            '大连' => '210200',
            '上海' => '310000',
            '天津' => '120000',
            '昆明' => '530100',
            '重庆' => '500000',
            '绍兴' => '330600',
            '义乌' => '330782',
            '湖州' => '330500',
            '佛山' => '440600',
            '宁波' => '330200',
            '杭州' => '330100',
            '石家庄' => '130100',
            '福州' => '350100',
            '温州' => '330300',
            '金华' => '330700',
            '廊坊' => '131000',
        ];
    }

    /**
     * 新一贷B类城市
     *
     * @return array
     */
    public static function getXinyidaiBCity()
    {
        return [
            '嘉兴' => '330400',
            '红河' => '532500',
            '台州' => '331000',
            '乐山' => '511100',
            '绵阳' => '510700',
            '日照' => '371100',
            '东营' => '370500',
            '淄博' => '370300',
            '烟台' => '370600',
            '潍坊' => '370700',
            '临沂' => '371300',
            '泰州' => '321200',
            '徐州' => '320300',
            '无锡' => '320200',
            '常州' => '320400',
            '荆州' => '421000',
            '襄阳' => '420600',
            '唐山' => '130200',
            '厦门' => '350200',
            '郑州' => '410100',
            '惠州' => '441300',
            '成都' => '510100',
            '南京' => '320100',
            '西安' => '610100',
            '泉州' => '350500',
            '太原' => '140100',
            '海口' => '460100',
            '中山' => '442000',
            '青岛' => '370200',
            '长沙' => '430100',
            '沈阳' => '210100',
            '珠海' => '440400',
            '南昌' => '360100',
            '南通' => '320600',
            '合肥' => '340100',
            '漳州' => '350600',
            '三亚' => '460200',
            '洛阳' => '410300',
            '南宁' => '450100',
        ];
    }


    /**
     * 根据城市获取编码
     *
     * @param $city
     * @return mixed|string
     */
    public static function getCityCode($city)
    {
        $cCode = '';
        $cityCode = SpreadStrategy::setCityCode();
        foreach ($cityCode as $key => $code) {
            if (strpos($city, $key) === false) {
                $cCode = '000000';
            } else {
                $cCode = $code;
            }
        }

        return $cCode;
    }

    /**
     * 黑牛保险符合的城市
     *
     * @return array
     */
    public static function getHeiNiuCity()
    {
        return [
            '佛山', '惠州', '柳州', '贵阳', '海口', '鄂州', '恩施', '黄冈', '黄石', '荆门', '荆州', '十堰', '随州', '天门', '武汉',
            '咸宁', '襄阳', '孝感', '宜昌', '郴州', '衡阳', '湘潭', '岳阳', '长沙', '株洲', '赣州', '吉安', '景德镇', '九江', '南昌',
            '上饶', '新余', '宜春', '巴中', '成都', '达州', '德阳', '广安', '广元', '乐山', '凉山彝族自治州', '泸州', '眉山', '绵阳',
            '南充', '遂宁', '宜宾', '资阳', '自贡', '昆明', '曲靖', '合肥', '德州', '济南', '济宁', '聊城', '泰安', '枣庄', '徐州',
            '盐城', '扬州', '南通', '东营', '临沂', '青岛', '威海', '潍坊', '淄博', '烟台', '福州', '宁德', '三明', '宁波', '泉州',
            '绍兴', '杭州', '湖州', '嘉兴', '金华', '台州', '北京', '大连', '保定', '沧州', '承德', '邯郸', '衡水', '廊坊', '秦皇岛',
            '石家庄', '唐山', '邢台', '张家口', '大庆', '哈尔滨', '佳木斯', '牡丹江', '齐齐哈尔', '吉林', '松原', '长春', '鞍山', '本溪',
            '朝阳', '丹东', '抚顺', '阜新', '葫芦岛', '锦州', '辽阳', '盘锦', '沈阳', '铁岭', '营口', '包头', '赤峰', '鄂尔多斯',
            '呼和浩特', '呼伦贝尔', '通辽', '天津', '安庆', '蚌埠', '亳州', '滁州', '阜阳', '淮北', '淮南', '黄山', '六安', '马鞍山', '铜陵',
            '芜湖', '宿州', '白银', '酒泉', '兰州', '庆阳', '天水', '武威', '安阳', '焦作', '洛阳', '濮阳', '三门峡', '新乡', '郑州',
            '漯河', '南阳', '平顶山', '商丘', '信阳', '许昌', '周口', '常州', '南京', '镇江', '西宁', '大同', '晋城', '临汾', '太原',
            '长治', '安康', '宝鸡', '汉中', '商洛', '铜川', '渭南', '西安', '咸阳', '延安', '榆林', '深圳', '苏州', '无锡', '昌吉',
            '哈密', '克拉玛依', '库尔勒', '石河子', '乌鲁木齐',
        ];
    }

    /**
     * 氧气贷城市列表
     */
    public static function getOxygendaiCity()
    {
        return [
            '包头', '吉林', '延边', '松原', '商洛', '达州', '丹东', '葫芦岛', '衢州', '哈尔滨', '安顺', '珠海', '海口', '天水', '红河', '大连', '烟台', '汕头',
            '上海', '淄博', '无锡', '苏州', '厦门', '东莞', '宁波', '昆明', '丽水', '蚌埠', '河源', '合肥', '武汉', '咸阳', '长沙', '太原', '九江', '济南', '三亚',
            '邯郸', '深圳', '赣州', '马鞍山', '南昌', '贵阳', '南京', '中山', '佛山', '重庆', '东营', '石家庄', '上饶', '南通', '钦州', '西安', '茂名', '芜湖', '兰州',
            '广州', '三门峡', '长春', '荆州', '新乡', '商丘', '呼和浩特', '常州', '曲靖', '玉溪', '开封', '四平', '株洲', '湘潭', '岳阳', '宿迁', '成都', '平顶山', '常德',
            '南宁', '潮州', '衡阳', '郑州', '宜昌', '宝鸡', '楚雄', '沈阳', '大理', '杭州', '黄山', '德州', '锦州', '淮安', '梧州', '福州', '滁州', '洛阳', '绵阳', '永州',
            '运城', '怀化', '西宁', '新疆', '邢台', '聊城', '桂林', '盘锦', '北京', '廊坊', '六安', '湛江', '扬州', '泰州', '徐州', '连云港', '镇江', '安阳', '漳州', '温州',
            '舟山', '威海', '青岛', '抚州', '嘉兴', '惠州', '淮北', '南充', '大庆', '许昌', '焦作', '江门', '吉安', '天津', '自贡', '景德镇', '黄石', '安康', '乌鲁木齐', '宜春',
            '营口', '泉州', '绍兴', '咸宁', '陇南', '武威', '白山', '牡丹江', '绥化', '汕尾', '鞍山', '保定', '沧州', '宣城', '昌吉', '十堰', '眉山', '鹰潭', '清远', '龙岩', '通化',
            '哈密', '德阳', '菏泽', '黔南', '齐齐哈尔', '宜宾', '贵港', '肇庆', '巴音郭楞', '随州', '湖州', '日照', '乐山', '毫州', '南阳', '临沂', '渭南', '大同', '滨州', '酒泉',
            '秦皇岛', '西双版纳', '长治', '荆门', '遵义', '唐山', '伊犁', '金华', '丽江', '内江', '泰安', '延安', '阳江', '新余', '宿州', '资阳', '泸州', '临汾', '玉林', '铁岭', '防城港',
            '承德', '柳州', '南平', '黄冈', '濮阳', '郴州', '枣庄', '襄阳', '晋城', '张掖', '晋中', '济宁', '阜阳', '百色', '孝感', '萍乡', '莆田', '三明', '晋中', '忻州', '儋州', '银川',
            '揭阳', '云浮', '台州', '衡水', '梅州', '韶关', '汉中', '张家口', '榆林',
        ];

    }

    /**
     * 小小金融城市列表
     */
    public static function getXiaoxiaoCity()
    {
        //获取小小金融的城市列表
        return [
            '北京', '上海', '广州', '深圳', '东莞', '杭州', '苏州',
        ];
    }

    /**
     * 获取真实的身份证号/姓名/性别/生日
     * @param int $page
     * @param array $params
     * @return array
     */
    public static function getUserInfo($page = 1, array $params = [])
    {
        $info = UserIdentityFactory::fetchUserRealInfo($params['user_id']);

        // 若认证,按照实名认证; 若未认证,根据填写的身份证号获取生日和性别
        if (!empty($info)) {
            // 认证过
            $params['certificate_no'] = $info['certificate_no'];
            $params['name'] = $info['name'];
            $params['sex'] = $info['sex'] ? 0 : 1;
            $params['birthday'] = $info['birthday'];
        } else {
            // 未认证
            if ($page == 2) {
                $params['certificate_no'] = UserSpreadFactory::getSpreadCertificateNo($params['mobile']);
            }

            $userInfo = Utils::getAgeAndBirthDayByCard($params['certificate_no']);
            $params['sex'] = $userInfo['sex'];
            $params['birthday'] = $userInfo['birthday'];
        }

        return $params;
    }

    /**
     * 对v2结果页中的是否有保险进行设置
     *
     * @param $typeIds
     * @return mixed
     */
    public static function checkInsuranceResult($typeIds)
    {
        //查询黑牛保险是否存在
        $type = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_HEINIU_NID);
        $heiniuTypeId = isset($type['id']) ? $type['id'] : 0;

        //查询有利保险是否存在
        $youLiTypeInfo = UserSpreadFactory::fetchSpreadTypeByNid(SpreadNidConstant::SPREAD_YOULI_NID);
        $youliTypeId = isset($youLiTypeInfo['id']) ? $youLiTypeInfo['id'] : 0;

        if (in_array($heiniuTypeId, $typeIds) && in_array($youliTypeId, $typeIds)) {
            $infoRes['content'] = '申请有礼：免费送您的平安/中英保险已经领取成功';
        } elseif (in_array($heiniuTypeId, $typeIds)) {
            $infoRes['content'] = '申请有礼：免费送您的平安保险已经领取成功';
        } elseif (in_array($youliTypeId, $typeIds)) {
            $infoRes['content'] = '申请有礼：免费送您的中英保险已经领取成功';
        } else {
            $infoRes['content'] = '';
        }
        $infoRes['typeIds'] = array_diff($typeIds, [$heiniuTypeId, $youliTypeId]);

        return $infoRes;
    }

    /**
     * 同步推广平台类型中限制条件
     *
     * @param array $spread
     * @param array $type
     * @param array $data
     * @return array
     */
    public static function getSpreadDatas($spread = [], $type = [], $data = [])
    {
        $spread['type_id'] = $type ? $type['id'] : 0;
        $spread['limit'] = $type ? $type['limit'] : 0;
        $spread['total'] = $type ? $type['total'] : 0;
        $spread['max_age'] = $type ? $type['max_age'] : 0;
        $spread['min_age'] = $type ? $type['min_age'] : 0;
        $spread['type_sex'] = $type ? $type['sex'] : 0;
        $spread['valid_start'] = $type ? $type['valid_start'] : 0;
        $spread['valid_end'] = $type ? $type['valid_end'] : 0;
        $spread['choice_status'] = $type ? $type['choice_status'] : 0;
        //延迟推送限制状态&时间
        if (isset($data['switch']) && $data['switch'] == 1) {
            $spread['batch_status'] = 0;
            $spread['batch_interval'] = 0;
        } else {
            $spread['batch_status'] = $type ? $type['batch_status'] : 0; //0,关闭　　1,开启
            $spread['batch_interval'] = $type ? $type['batch_interval'] : 0; //单位：分钟
        }

        return $spread;
    }

    /**
     * 验证分发时间
     * @param array $params
     * @return bool
     */
    public static function checkValidateTime($params = [])
    {
        //不进行时间限制
        if (empty($params['valid_start']) && empty($params['valid_end'])) {
            return true;
        }
        //当前时间
        $dayTime = date('Y-m-d H:i:s', time());
        //开始时间
        $validStart = date('Y-m-d ') . $params['valid_start'] . ':00';
        //结束时间
        $validEnd = date('Y-m-d ') . $params['valid_end'] . ':00';

        if ($dayTime >= $validStart && $dayTime <= $validEnd) {
            return true;
        }

        return false;
    }

    /**
     * 推广验证性别匹配
     *
     * @param array $params
     * @return bool
     */
    public static function checkSpreadSex($params = [])
    {
        //没有性别限制
        if (empty($params['type_sex'])) {
            return true;
        }

        if ($params['sex'] == 1) {
            //男
            $params['sex'] = 1;
        } elseif ($params['sex'] == 0) {
            //女
            $params['sex'] = 2;
        }

        if ($params['sex'] == $params['type_sex']) {
            return true;
        }

        return false;
    }

    /**
     * 推广验证年龄
     *
     * @param array $params
     * @return bool
     */
    public static function checkSpreadAge($params = [])
    {
        //没有年龄限制
        if (empty($params['max_age']) && empty($params['min_age'])) {
            return true;
        }

        if ($params['age'] >= $params['min_age'] && $params['age'] <= $params['max_age']) {
            return true;
        }

        return false;
    }

    /**
     * 推送状态统计 包含城市统计
     *  choice_status 0,全 1,成功　2,失败
     *  response_code 0,全 1,成功　2,失败
     * @param array $params
     * @return bool
     */
    public static function updateSpreadCounts($params = [])
    {

        if (0 == $params['choice_status']) //全部
        {
            //更新推广配置次数统计
            UserSpreadFactory::updateSpreadTypeOnlyTotal($params['type_nid']);
            //更新城市推送次数统计
            UserSpreadFactory::updateUserSpreadTypeAreasRelOnlyTotal($params);

        } elseif (1 == $params['choice_status'] && 1 == $params['response_code']) //成功
        {
            //更新推广配置次数统计
            UserSpreadFactory::updateSpreadTypeOnlyTotal($params['type_nid']);
            //更新城市推送次数统计
            UserSpreadFactory::updateUserSpreadTypeAreasRelOnlyTotal($params);

        } elseif (2 == $params['choice_status'] && 2 == $params['response_code'])//失败
        {
            //更新推广配置次数统计
            UserSpreadFactory::updateSpreadTypeOnlyTotal($params['type_nid']);
            //更新城市推送次数统计
            UserSpreadFactory::updateUserSpreadTypeAreasRelOnlyTotal($params);
        }

        //统计总推送次数，总成功次数，总失败次数
        UserSpreadFactory::updateSpreadTypeByTotalAndStatus($params);
        //推广城市总次数统计、成功总次数、失败总次数
        return UserSpreadFactory::updateUserSpreadTypeAreasRelByTotalAndStatus($params);

    }

    /**
     *  推送状态统计 不包含城市统计
     * @param array $params
     * @return mixed
     */
    public static function updateSpreadCount($params = [])
    {
        if (0 == $params['choice_status']) //全部
        {
            //更新推广配置次数统计
            UserSpreadFactory::updateSpreadTypeOnlyTotal($params['type_nid']);

        } elseif (1 == $params['choice_status'] && 1 == $params['response_code']) //成功
        {
            //更新推广配置次数统计
            UserSpreadFactory::updateSpreadTypeOnlyTotal($params['status']);

        } elseif (2 == $params['choice_status'] && 2 == $params['response_code'])//失败
        {
            //更新推广配置次数统计
            UserSpreadFactory::updateSpreadTypeOnlyTotal($params['status']);
        }

        //统计总推送次数，总成功次数，总失败次数
        return UserSpreadFactory::updateSpreadTypeByTotalAndStatus($params);
    }

    /**
     * 用户填写完整信息分组整理
     * @param array $data
     * @return array
     */
    public static function getUserInfoByGroup($data = [])
    {
        $user = [];
        $user_a = SpreadConstant::SPREAD_GROUP_A;
        $user_b = SpreadConstant::SPREAD_GROUP_B;
        $user_c = SpreadConstant::SPREAD_GROUP_C;
        //A组
        $user[$user_a]['occupation'] = isset($data['occupation']) ? $data['occupation'] : '';
        $user[$user_a]['salary_extend'] = isset($data['salary_extend']) ? $data['salary_extend'] : '';
        $user[$user_a]['salary'] = isset($data['salary']) ? $data['salary'] : '';
        $user[$user_a]['accumulation_fund'] = isset($data['accumulation_fund']) ? $data['accumulation_fund'] : '';
        $user[$user_a]['work_hours'] = isset($data['work_hours']) ? $data['work_hours'] : '';
        $user[$user_a]['business_licence'] = isset($data['business_licence']) ? $data['business_licence'] : '';
        $user[$user_a]['social_security'] = isset($data['social_security']) ? $data['social_security'] : '';
        //B组
        $user[$user_b]['house_info'] = isset($data['house_info']) ? $data['house_info'] : '';
        $user[$user_b]['car_info'] = isset($data['car_info']) ? $data['car_info'] : '';
        $user[$user_b]['has_insurance'] = isset($data['has_insurance']) ? $data['has_insurance'] : '';
        //C组
        $user[$user_c]['has_creditcard'] = isset($data['has_creditcard']) ? $data['has_creditcard'] : '';
        $user[$user_c]['is_micro'] = isset($data['is_micro']) ? $data['is_micro'] : '';

        return $user ? $user : [];
    }

    /**
     * 信用信息进度
     * @param array $info
     * @return array
     */
    public static function getSpreadInfoProgress($info = [])
    {
        $params = [];
        //总进度
        $params['count'] = 0;
        //基本信息
        $basicInfo['name'] = isset($info['name']) ? $info['name'] : '';
        $basicInfo['certificate_no'] = isset($info['certificate_no']) ? $info['certificate_no'] : '';
        $basicInfo['city'] = isset($info['city']) ? $info['city'] : '';
        $basicInfo = array_diff($basicInfo, ['']);
        $basicCount = count($basicInfo);
        $params['basicSign'] = 0;
        if ($basicCount == SpreadConstant::PROGRESS_BASIC_COUNT) //计算
        {
            $params['count'] += SpreadConstant::PROGRESS_BASIC_VALUE;
            $params['basicSign'] = 1;
        }

        //工作信息
        $params['workSign'] = 0;
        $workInfo['occupation'] = isset($info['occupation']) ? $info['occupation'] : '';
        $workInfo['salary'] = isset($info['salary']) ? $info['salary'] : '';
        //上班族
        if ($workInfo['occupation'] == '001')//上班族
        {
            $workInfo['salary_extend'] = isset($info['salary_extend']) ? $info['salary_extend'] : '';
            $workInfo['work_hours'] = isset($info['work_hours']) ? $info['work_hours'] : '';
            $workInfo['accumulation_fund'] = isset($info['accumulation_fund']) ? $info['accumulation_fund'] : '';
            $workInfo['has_insurance'] = isset($info['has_insurance']) ? $info['has_insurance'] : '';
            $workInfo = array_diff($workInfo, ['']);
            $workCount = count($workInfo);
            if ($workCount == SpreadConstant::PROGRESS_WORKING_OFFICE_COUNT)//计算
            {
                $params['count'] += SpreadConstant::PROGRESS_WORKING_VALUE;
                $params['workSign'] = 1;
            }

        } elseif ($workInfo['occupation'] == '002')//公务员
        {
            $workInfo['work_hours'] = isset($info['work_hours']) ? $info['work_hours'] : '';
            $workInfo['accumulation_fund'] = isset($info['accumulation_fund']) ? $info['accumulation_fund'] : '';
            $workInfo = array_diff($workInfo, ['']);
            $workCount = count($workInfo);
            if ($workCount == SpreadConstant::PROGRESS_WORKING_CIVIL_COUNT)//计算
            {
                $params['count'] += SpreadConstant::PROGRESS_WORKING_VALUE;
                $params['workSign'] = 1;
            }
        } elseif ($workInfo['occupation'] == '003')//私营企业主
        {
            $workInfo['business_licence'] = isset($info['business_licence']) ? $info['business_licence'] : '';
            $workInfo = array_diff($workInfo, ['']);
            $workCount = count($workInfo);
            if ($workCount == SpreadConstant::PROGRESS_WORKING_CIVIL_COUNT)//计算
            {
                $params['count'] += SpreadConstant::PROGRESS_WORKING_VALUE;
                $params['workSign'] = 1;
            }
        }

        //资产信息
        $params['propertySign'] = 0;
        $propertyInfo['has_insurance'] = isset($info['has_insurance']) ? $info['has_insurance'] : '';
        $propertyInfo['house_info'] = isset($info['house_info']) ? $info['house_info'] : '';
        $propertyInfo['car_info'] = isset($info['car_info']) ? $info['car_info'] : '';
        $propertyInfo = array_diff($propertyInfo, ['']);
        $propertyCount = count($propertyInfo);
        if ($propertyCount == SpreadConstant::PROGRESS_PROPERTY_COUNT)//计算
        {
            $params['count'] += SpreadConstant::PROGRESS_PROPERTY_VALUE;
            $params['propertySign'] = 1;
        }

        //信用信息
        $params['creditSign'] = 0;
        $creditInfo['has_creditcard'] = isset($info['has_creditcard']) ? $info['has_creditcard'] : '';
        $creditInfo['is_micro'] = isset($info['is_micro']) ? $info['is_micro'] : '';
        $creditInfo = array_diff($creditInfo, ['']);
        $creditCount = count($creditInfo);
        if ($creditCount == SpreadConstant::PROGRESS_CREDIT_COUNT)//计算
        {
            $params['count'] += SpreadConstant::PROGRESS_CREDIT_VALUE;
            $params['creditSign'] = 1;
        }

        return $params;
    }

    /**
     * 月收入
     * @param $salary
     * @return mixed|string
     */
    public static function getSalary($salary)
    {
        $tmp = [
            '001' => '0~2000',
            '002' => '2000~5000',
            '003' => '5000~10000',
            '004' => '1w',
            '101' => '0~2000',
            '102' => '2000~3000',
            '103' => '3000~4000',
            '104' => '4000~5000',
            '105' => '5000~10000',
            '106' => '1w',
        ];

        if (isset($tmp[$salary])) {
            return $tmp[$salary];
        }

        return '';
    }

    /**
     * 月收入范围, 001:2000以下，002:2000-5000,003:5000-1万，004：1万以上
     * @param array $params
     * @return int
     */
    public static function formatSalaryAverage($params = [])
    {
        switch ($params['salary']) {
            case '001':
                $salaryVal = 2000;
                break;
            case '002':
                $salaryVal = bcdiv(bcadd(2000, 5000), 2);
                break;
            case '003':
                $salaryVal = bcdiv(bcadd(5000, 10000), 2);
                break;
            case '004':
                $salaryVal = 10000;
                break;
            case '101':
                $salaryVal = 2000;
                break;
            case '102':
                $salaryVal = bcdiv(bcadd(2000, 3000), 2);
                break;
            case '103':
                $salaryVal = bcdiv(bcadd(3000, 4000), 2);
                break;
            case '104':
                $salaryVal = bcdiv(bcadd(4000, 5000), 2);
                break;
            case '105':
                $salaryVal = bcdiv(bcadd(5000, 10000), 2);
                break;
            case '106':
                $salaryVal = 10000;
                break;
            default:
                $salaryVal = 0;
        }

        return intval($salaryVal);
    }
}