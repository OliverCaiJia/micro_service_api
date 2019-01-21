<?php

namespace App\Services\Core\Zhima\SDK\Zmop\Request;
/**
 * ZHIMA API: zhima.credit.hetrone.dasscore.query Request
 *
 * @author auto create
 * @since 1.0, 2017-08-22 15:13:15
 */
class ZhimaCreditHetroneDasscoreQueryRequest
{
	/** 
	 * 
	 **/
	private $amtBankcardTransacThreeMonths;
	
	/** 
	 * 
	 **/
	private $cntBankcardTransacTwelveMonths;
	
	/** 
	 * 
	 **/
	private $cntMobileOnline;
	
	/** 
	 * 
	 **/
	private $contactScore;
	
	/** 
	 * 
	 **/
	private $existsBankcardTransacOversea;
	
	/** 
	 * 
	 **/
	private $gender;
	
	/** 
	 * 
	 **/
	private $openId;
	
	/** 
	 * 
	 **/
	private $productCode;
	
	/** 
	 * 
	 **/
	private $transactionId;

	private $apiParas = array();
	private $fileParas = array();
	private $apiVersion="1.0";
	private $scene;
	private $channel;
	private $platform;
	private $extParams;

	
	public function setAmtBankcardTransacThreeMonths($amtBankcardTransacThreeMonths)
	{
		$this->amtBankcardTransacThreeMonths = $amtBankcardTransacThreeMonths;
		$this->apiParas["amt_bankcard_transac_three_months"] = $amtBankcardTransacThreeMonths;
	}

	public function getAmtBankcardTransacThreeMonths()
	{
		return $this->amtBankcardTransacThreeMonths;
	}

	public function setCntBankcardTransacTwelveMonths($cntBankcardTransacTwelveMonths)
	{
		$this->cntBankcardTransacTwelveMonths = $cntBankcardTransacTwelveMonths;
		$this->apiParas["cnt_bankcard_transac_twelve_months"] = $cntBankcardTransacTwelveMonths;
	}

	public function getCntBankcardTransacTwelveMonths()
	{
		return $this->cntBankcardTransacTwelveMonths;
	}

	public function setCntMobileOnline($cntMobileOnline)
	{
		$this->cntMobileOnline = $cntMobileOnline;
		$this->apiParas["cnt_mobile_online"] = $cntMobileOnline;
	}

	public function getCntMobileOnline()
	{
		return $this->cntMobileOnline;
	}

	public function setContactScore($contactScore)
	{
		$this->contactScore = $contactScore;
		$this->apiParas["contact_score"] = $contactScore;
	}

	public function getContactScore()
	{
		return $this->contactScore;
	}

	public function setExistsBankcardTransacOversea($existsBankcardTransacOversea)
	{
		$this->existsBankcardTransacOversea = $existsBankcardTransacOversea;
		$this->apiParas["exists_bankcard_transac_oversea"] = $existsBankcardTransacOversea;
	}

	public function getExistsBankcardTransacOversea()
	{
		return $this->existsBankcardTransacOversea;
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

	public function setOpenId($openId)
	{
		$this->openId = $openId;
		$this->apiParas["open_id"] = $openId;
	}

	public function getOpenId()
	{
		return $this->openId;
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

	public function setTransactionId($transactionId)
	{
		$this->transactionId = $transactionId;
		$this->apiParas["transaction_id"] = $transactionId;
	}

	public function getTransactionId()
	{
		return $this->transactionId;
	}

	public function getApiMethodName()
	{
		return "zhima.credit.hetrone.dasscore.query";
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
