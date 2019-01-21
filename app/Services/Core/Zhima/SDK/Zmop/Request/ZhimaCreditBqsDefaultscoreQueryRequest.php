<?php

namespace App\Services\Core\Zhima\SDK\Zmop\Request;

/**
 * ZHIMA API: zhima.credit.bqs.defaultscore.query Request
 *
 * @author auto create
 * @since 1.0, 2017-08-23 16:30:19
 */
class ZhimaCreditBqsDefaultscoreQueryRequest
{
	/** 
	 * 
	 **/
	private $acceptPercentApply;
	
	/** 
	 * 
	 **/
	private $age;
	
	/** 
	 * 
	 **/
	private $applyHour;
	
	/** 
	 * 
	 **/
	private $applyPartnerTypeCount;
	
	/** 
	 * 
	 **/
	private $blackCount;
	
	/** 
	 * 
	 **/
	private $callActiveArea;
	
	/** 
	 * 
	 **/
	private $contactExcludedCount;
	
	/** 
	 * 
	 **/
	private $contactsActiveArea;
	
	/** 
	 * 
	 **/
	private $deviceCount;
	
	/** 
	 * 
	 **/
	private $gender;
	
	/** 
	 * 
	 **/
	private $gpsCityCount;
	
	/** 
	 * 
	 **/
	private $inactiveDays;
	
	/** 
	 * 
	 **/
	private $ipCityCount;
	
	/** 
	 * 
	 **/
	private $loanAppCount;
	
	/** 
	 * 
	 **/
	private $mobile;
	
	/** 
	 * 
	 **/
	private $multiapplyCount;
	
	/** 
	 * 
	 **/
	private $nightCalls;
	
	/** 
	 * 
	 **/
	private $noneMobileCount;
	
	/** 
	 * 
	 **/
	private $onlyTerminCount;
	
	/** 
	 * 
	 **/
	private $openDays;
	
	/** 
	 * 
	 **/
	private $openId;
	
	/** 
	 * 
	 **/
	private $phoneDays;
	
	/** 
	 * 
	 **/
	private $productCode;
	
	/** 
	 * 
	 **/
	private $provinceId;
	
	/** 
	 * 
	 **/
	private $rejectPercentApply;
	
	/** 
	 * 
	 **/
	private $sumInfoCostMoney;
	
	/** 
	 * 
	 **/
	private $topContact;
	
	/** 
	 * 
	 **/
	private $transactionId;
	
	/** 
	 * 
	 **/
	private $whiteGrade;
	
	/** 
	 * 
	 **/
	private $workCityCount;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setAcceptPercentApply($acceptPercentApply)
	{
		$this->acceptPercentApply = $acceptPercentApply;
		$this->apiParas["accept_percent_apply"] = $acceptPercentApply;
	}

	public function getAcceptPercentApply()
	{
		return $this->acceptPercentApply;
	}

	public function setAge($age)
	{
		$this->age = $age;
		$this->apiParas["age"] = $age;
	}

	public function getAge()
	{
		return $this->age;
	}

	public function setApplyHour($applyHour)
	{
		$this->applyHour = $applyHour;
		$this->apiParas["apply_hour"] = $applyHour;
	}

	public function getApplyHour()
	{
		return $this->applyHour;
	}

	public function setApplyPartnerTypeCount($applyPartnerTypeCount)
	{
		$this->applyPartnerTypeCount = $applyPartnerTypeCount;
		$this->apiParas["apply_partner_type_count"] = $applyPartnerTypeCount;
	}

	public function getApplyPartnerTypeCount()
	{
		return $this->applyPartnerTypeCount;
	}

	public function setBlackCount($blackCount)
	{
		$this->blackCount = $blackCount;
		$this->apiParas["black_count"] = $blackCount;
	}

	public function getBlackCount()
	{
		return $this->blackCount;
	}

	public function setCallActiveArea($callActiveArea)
	{
		$this->callActiveArea = $callActiveArea;
		$this->apiParas["call_active_area"] = $callActiveArea;
	}

	public function getCallActiveArea()
	{
		return $this->callActiveArea;
	}

	public function setContactExcludedCount($contactExcludedCount)
	{
		$this->contactExcludedCount = $contactExcludedCount;
		$this->apiParas["contact_excluded_count"] = $contactExcludedCount;
	}

	public function getContactExcludedCount()
	{
		return $this->contactExcludedCount;
	}

	public function setContactsActiveArea($contactsActiveArea)
	{
		$this->contactsActiveArea = $contactsActiveArea;
		$this->apiParas["contacts_active_area"] = $contactsActiveArea;
	}

	public function getContactsActiveArea()
	{
		return $this->contactsActiveArea;
	}

	public function setDeviceCount($deviceCount)
	{
		$this->deviceCount = $deviceCount;
		$this->apiParas["device_count"] = $deviceCount;
	}

	public function getDeviceCount()
	{
		return $this->deviceCount;
	}

	public function setGender($gender)
	{
		$this->gender = $gender;
		$this->apiParas["gender"] = $gender;
	}

	public function getGender()
	{
		return $this->gender;
	}

	public function setGpsCityCount($gpsCityCount)
	{
		$this->gpsCityCount = $gpsCityCount;
		$this->apiParas["gps_city_count"] = $gpsCityCount;
	}

	public function getGpsCityCount()
	{
		return $this->gpsCityCount;
	}

	public function setInactiveDays($inactiveDays)
	{
		$this->inactiveDays = $inactiveDays;
		$this->apiParas["inactive_days"] = $inactiveDays;
	}

	public function getInactiveDays()
	{
		return $this->inactiveDays;
	}

	public function setIpCityCount($ipCityCount)
	{
		$this->ipCityCount = $ipCityCount;
		$this->apiParas["ip_city_count"] = $ipCityCount;
	}

	public function getIpCityCount()
	{
		return $this->ipCityCount;
	}

	public function setLoanAppCount($loanAppCount)
	{
		$this->loanAppCount = $loanAppCount;
		$this->apiParas["loan_app_count"] = $loanAppCount;
	}

	public function getLoanAppCount()
	{
		return $this->loanAppCount;
	}

	public function setMobile($mobile)
	{
		$this->mobile = $mobile;
		$this->apiParas["mobile"] = $mobile;
	}

	public function getMobile()
	{
		return $this->mobile;
	}

	public function setMultiapplyCount($multiapplyCount)
	{
		$this->multiapplyCount = $multiapplyCount;
		$this->apiParas["multiapply_count"] = $multiapplyCount;
	}

	public function getMultiapplyCount()
	{
		return $this->multiapplyCount;
	}

	public function setNightCalls($nightCalls)
	{
		$this->nightCalls = $nightCalls;
		$this->apiParas["night_calls"] = $nightCalls;
	}

	public function getNightCalls()
	{
		return $this->nightCalls;
	}

	public function setNoneMobileCount($noneMobileCount)
	{
		$this->noneMobileCount = $noneMobileCount;
		$this->apiParas["none_mobile_count"] = $noneMobileCount;
	}

	public function getNoneMobileCount()
	{
		return $this->noneMobileCount;
	}

	public function setOnlyTerminCount($onlyTerminCount)
	{
		$this->onlyTerminCount = $onlyTerminCount;
		$this->apiParas["only_termin_count"] = $onlyTerminCount;
	}

	public function getOnlyTerminCount()
	{
		return $this->onlyTerminCount;
	}

	public function setOpenDays($openDays)
	{
		$this->openDays = $openDays;
		$this->apiParas["open_days"] = $openDays;
	}

	public function getOpenDays()
	{
		return $this->openDays;
	}

	public function setOpenId($openId)
	{
		$this->openId = $openId;
		$this->apiParas["open_id"] = $openId;
	}

	public function getOpenId()
	{
		return $this->openId;
	}

	public function setPhoneDays($phoneDays)
	{
		$this->phoneDays = $phoneDays;
		$this->apiParas["phone_days"] = $phoneDays;
	}

	public function getPhoneDays()
	{
		return $this->phoneDays;
	}

	public function setProductCode($productCode)
	{
		$this->productCode = $productCode;
		$this->apiParas["product_code"] = $productCode;
	}

	public function getProductCode()
	{
		return $this->productCode;
	}

	public function setProvinceId($provinceId)
	{
		$this->provinceId = $provinceId;
		$this->apiParas["province_id"] = $provinceId;
	}

	public function getProvinceId()
	{
		return $this->provinceId;
	}

	public function setRejectPercentApply($rejectPercentApply)
	{
		$this->rejectPercentApply = $rejectPercentApply;
		$this->apiParas["reject_percent_apply"] = $rejectPercentApply;
	}

	public function getRejectPercentApply()
	{
		return $this->rejectPercentApply;
	}

	public function setSumInfoCostMoney($sumInfoCostMoney)
	{
		$this->sumInfoCostMoney = $sumInfoCostMoney;
		$this->apiParas["sum_info_cost_money"] = $sumInfoCostMoney;
	}

	public function getSumInfoCostMoney()
	{
		return $this->sumInfoCostMoney;
	}

	public function setTopContact($topContact)
	{
		$this->topContact = $topContact;
		$this->apiParas["top_contact"] = $topContact;
	}

	public function getTopContact()
	{
		return $this->topContact;
	}

	public function setTransactionId($transactionId)
	{
		$this->transactionId = $transactionId;
		$this->apiParas["transaction_id"] = $transactionId;
	}

	public function getTransactionId()
	{
		return $this->transactionId;
	}

	public function setWhiteGrade($whiteGrade)
	{
		$this->whiteGrade = $whiteGrade;
		$this->apiParas["white_grade"] = $whiteGrade;
	}

	public function getWhiteGrade()
	{
		return $this->whiteGrade;
	}

	public function setWorkCityCount($workCityCount)
	{
		$this->workCityCount = $workCityCount;
		$this->apiParas["work_city_count"] = $workCityCount;
	}

	public function getWorkCityCount()
	{
		return $this->workCityCount;
	}

	public function getApiMethodName()
	{
		return "zhima.credit.bqs.defaultscore.query";
	}

	public function setScene($scene)
	{
		$this->scene=$scene;
	}

	public function getScene()
	{
		return $this->scene;
	}
	
	public function setChannel($channel)
	{
		$this->channel=$channel;
	}

	public function getChannel()
	{
		return $this->channel;
	}
	
	public function setPlatform($platform)
	{
		$this->platform=$platform;
	}

	public function getPlatform()
	{
		return $this->platform;
	}

	public function setExtParams($extParams)
	{
		$this->extParams=$extParams;
	}

	public function getExtParams()
	{
		return $this->extParams;
	}	

	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function getFileParas()
	{
		return $this->fileParas;
	}

	public function setApiVersion($apiVersion)
	{
		$this->apiVersion=$apiVersion;
	}

	public function getApiVersion()
	{
		return $this->apiVersion;
	}

}
