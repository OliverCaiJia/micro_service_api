<?php
namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Helpers\RestResponseFactory;
use App\Helpers\RestUtils;
use App\Helpers\Utils;
use App\Models\Factory\BairongFactory;
use App\Models\Factory\DataFactory;
use App\Services\Core\Data\Bairong\BairongService;
use App\Strategies\DataStrategy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Core\Data\Insurance\InsuranceService;
use App\Models\Factory\UserFactory;
use App\Models\Factory\UserInsuranceFactory;
use App\Constants\UserInsuranceConstant;




/**
 * Class DataController
 * @package App\Http\Controllers\V2
 * 对接接口数据
 */
class DataController extends Controller
{
    /**
     * 百融
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBairongQuery(Request $request)
    {
        $params = $request->input();
        $dataKey = BairongFactory::BAIRONG_PHONE_DATA. '_' .$params['mobile'];
        $watchKey = BairongFactory::BAIRONG_PHONE_ONE. '_' .$params['mobile'];
        $watch = BairongFactory::incrementCache($watchKey);
        if ($watch == 1)
        {
            $result = BairongService::getQueryData($params['mobile']);
            $params['code'] = isset($result['code']) ? $result['code'] : 0;
            $params['swift_number'] =  isset($result['swift_number']) ? $result['swift_number'] : 0;
            $params['content'] = json_encode($result, JSON_UNESCAPED_UNICODE);
            //入库
            DataFactory::insertBairongLog($params);

            if (empty($result) || !isset($result['Product']) || $result['Product']['status'] != '00' || $result['code'] != '600000')
            {
                //新用户暂时没有数据,删除watch
                BairongFactory::delCache($watchKey);
                //报错
                return RestResponseFactory::ok([], RestUtils::getErrorMessage(1005), 1005);
            }
            $lists = $result['Product']['data'];
            $productLists = DataStrategy::getBairongProductList();
            foreach ($lists as $list)
            {
                $arr[] = $productLists[$list];
            }

            //将数据缓存在redis中
            BairongFactory::setCache($dataKey, $arr, Carbon::now()->addHour(24));
        }
        else
        {
            //从缓存中取数据
            $arr = BairongFactory::getCache($dataKey);
            if (empty($arr))
            {
                //删除watch,重新设置开关
                BairongFactory::delCache($watchKey);
            }
        }

        return (empty($arr)) ? RestResponseFactory::ok([],RestUtils::getErrorMessage(1005),1005) : RestResponseFactory::ok($arr);
    }

    /**
     * 保险
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function  applyInsurance(Request $request)
    {
        $userid  = $request->user()->sd_user_id;
        $data = UserFactory::getRealInfoByUserId($userid);

        if ($data)
        {
            //根据身份证号查找年龄，并判断
            $age = Utils::getAgeByID($data['certificate_no']);
            
            if($age<25 || $age>50)
            {
              return RestResponseFactory::ok(RestUtils::getStdObj(), '年龄必须在25和50之间');
            }
            $data['mobile'] = $request->user()->mobile;
            $data['remark'] = UserInsuranceConstant::REMARK;
            $data['channel_num'] = UserInsuranceConstant::CHANNEL_NUM;
            $data['type_nid'] = UserInsuranceConstant::TYPE_NID;
            $data['created_ip'] =  Utils::ipAddress();
        }else
        {
            return RestResponseFactory::ok(RestUtils::getStdObj(), '该用户未进行认证');
        }


        //创建一条保险记录
        if(UserInsuranceFactory::createInsurance($data))
        {
            //成功之后,把数据传到保险接口，并返回相关数据
            $result  = InsuranceService::i()->getInsurance($data);

            //更新到数据库
            $insurance = UserInsuranceFactory::updatedInsurance($data['user_id'],$result);
        }

        return isset($insurance)?RestResponseFactory::ok($insurance):RestResponseFactory::ok(RestUtils::getStdObj(),'领取失败');

    }


}