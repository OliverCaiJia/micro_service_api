<?php

namespace App\Strategies;

use App\Strategies\AppStrategy;

/**
 * 短信策略
 *
 * @package App\Strategies
 */
class SmsStrategy extends AppStrategy
{
	/**
	 * @param string $inviteLink
	 * @return string
	 * 短信邀请
	 */
	public static function getSmsContent($inviteLink = '')
	{
		return $smsContent = '邀请您加入速贷之家-极速贷款，上速贷之家。 ' . $inviteLink;
	}
	
	/**
	 * @param $mobile
	 * 获取 codeKey signKey
	 * phone普通手机号，forgetpwd忘记密码，password修改密码，updatephone修改手机号，register注册
	 */
	public static function getCodeKeyAndSignKey($mobile = '', $type = '')
	{
		switch ($type) {
			case 'phone':
				//手机号
				$codeArr['codeKey'] = 'mobile_code_' . $mobile;
				$codeArr['signKey'] = 'mobile_random_' . $mobile;
				break;
			case 'forgetpwd':
				//忘记密码
				$codeArr['codeKey'] = 'forget_password_code_' . $mobile;
				$codeArr['signKey'] = 'forget_password_random_' . $mobile;
				break;
			case 'password':
				//修改密码
				$codeArr['codeKey'] = 'password_code_' . $mobile;
				$codeArr['signKey'] = 'password_random_' . $mobile;
				break;
			case 'updatephone':
				//修改手机号
				$codeArr['codeKey'] = 'update_mobile_code_' . $mobile;
				$codeArr['signKey'] = 'update_mobile_random_' . $mobile;
				break;
			case 'register':
				//注册
				$codeArr['codeKey'] = 'login_phone_code_' . $mobile;
				$codeArr['signKey'] = 'login_random_' . $mobile;
				break;
			default:
				//修改手机号
				$codeArr['codeKey'] = 'mobile_code_' . $mobile;
				$codeArr['signKey'] = 'mobile_random_' . $mobile;
				break;
		}
		return $codeArr;
	}
}