<?php

namespace App\Http\Controllers\View;

use App\Constants\HelpConstant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 帮助中心、设置相关
 * Class AgreementController
 * @package App\Http\Controllers\View
 */
class HelpController extends Controller
{
    /**
     * 获取协议内容
     * @param Request $request
     * @return \Illuminate\View\View|string
     */
    public function fetchAgreement(Request $request)
    {
        $id = $request->input('id');

        switch ($id) {
            case 1:
                //速贷之家《注册服务协议》
                return view('app.jdt.users.use_agreement');
                break;
            case 2:
                //速贷之家《会员服务协议》
                return view('app.jdt.users.membership_agreement');
                break;
            case 3:
                //闪信《授权协议》
                return view('app.jdt.credit_report.credit_report_agreement');
                break;
            default:
                return '';
        }
    }

    /**
     * 设置 - 协议
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchAgreements()
    {
        $agreements = HelpConstant::AGREEMENTS;

        return view('app.jdt.help.agreements', ['data' => $agreements]);
    }
}