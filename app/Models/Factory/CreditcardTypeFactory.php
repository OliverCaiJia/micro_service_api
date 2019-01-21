<?php

namespace App\Models\Factory;

use App\Models\AbsModelFactory;
use App\Models\Orm\BankCreditcardDegree;
use App\Models\Orm\BankCreditcardFeeType;
use App\Models\Orm\BankCreditcardUsage;
use App\Models\Orm\BankCreditcardUsageType;
use App\Models\Orm\BankImage;
use App\Models\Orm\BankImageType;
use App\Models\Orm\BankSpecial;
use App\Models\Orm\BankSpecialType;

/**
 * Class CreditcardTypeFactory
 * @package App\Models\Factory
 * 信用卡用途工厂
 */
class CreditcardTypeFactory extends AbsModelFactory
{
    /**
     * @return array
     * @status  是否显示, 1 显示, 0 不显示
     * 银行信用卡卡片用途类型表
     */
    public static function fetchUsageType()
    {
        $usages = BankCreditcardUsageType::select(['id', 'name', 'type_nid'])
            ->where(['status' => 1])
            ->get()->toArray();

        return $usages ? $usages : [];
    }

    /**
     * @return array
     * @status  是否显示, 1 显示, 0 不显示
     * 银行信用卡年费类型表
     */
    public static function fetchFeeType()
    {
        $degrees = BankCreditcardFeeType::select(['id', 'name', 'type_nid'])
            ->where(['status' => 1])
            ->get()->toArray();

        return $degrees ? $degrees : [];
    }

    /**
     * @param $data
     * @return string
     * @status 是否显示, 1 显示, 0 不显示
     * 银行信用卡卡片用途了类型
     */
    public static function fetchUsageIdByTypeNid($typeNid)
    {
        $type = BankCreditcardUsageType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $type ? $type->id : '';
    }

    /**
     * @param $usageId
     * @return array
     * @is_delete 是否删除, 0否, 1是
     * 银行信用卡用途对应的信用卡id
     */
    public static function fetchCreditcardIdByUsageId($usageId)
    {
        $ids = BankCreditcardUsage::select(['credit_id'])
            ->where(['usage_id' => $usageId])
            ->where(['is_delete' => 0])
            ->pluck('credit_id')->toArray();

        return $ids ? $ids : [];
    }

    /**
     * @param $degree
     * @return array
     * @is_delete 是否删除, 0否, 1是
     * 银行信用卡等级对应的信用卡id
     */
    public static function fetchCreditcardIdByDegreeId($degree)
    {
        $ids = BankCreditcardDegree::select(['credit_id'])
            ->where(['degree' => $degree])
            ->where(['is_delete' => 0])
            ->pluck('credit_id')->toArray();

        return $ids ? $ids : [];
    }

    /**
     * @param $typeNid
     * @return string
     * @status 是否显示, 1 显示, 0 不显示
     */
    public static function fetchFeeIdByTypeNid($typeNid)
    {
        $type = BankCreditcardFeeType::select(['id'])
            ->where(['type_nid' => $typeNid, 'status' => 1])
            ->first();

        return $type ? $type->id : '';
    }

    /**
     * @param $typeNid
     * @return string
     * @status 是否显示 1显示, 0不显示
     * 特色精选类型
     */
    public static function fetchSpecialIdByTypeNid($typeNid)
    {
        $id = BankSpecialType::select(['id'])
            ->where(['status' => 1, 'type_nid' => $typeNid])
            ->first();

        return $id ? $id->id : '';
    }

    /**
     * @param $specialId
     * @return array
     * @status 图片使用状态 0未使用,1使用
     */
    public static function fetchSpecialImages($specialId)
    {
        $specialImages = BankSpecial::select(['id', 'name', 'special_link', 'type_nid'])
            ->where(['status' => 1, 'type_id' => $specialId])
            ->get()->toArray();

        return $specialImages ? $specialImages : [];
    }

    /**
     * @param $typeNid
     * @return string
     * @status 是否显示 1显示, 0不显示
     * 用途卡片类型id
     */
    public static function fetchImageIdByTypeNid($typeNid)
    {
        $id = BankImageType::select(['id'])
            ->where(['status' => 1, 'type_nid' => $typeNid])
            ->first();

        return $id ? $id->id : '';
    }

    /**
     * @param $imageId
     * @return array
     * @status 是否显示 1显示, 0不显示
     * 用途图片
     */
    public static function fetchUsageImages($imageId)
    {
        $time = date('Y-m-d H:i:s', time());
        $images = BankImage::select(['id', 'name', 'img_link', 'usage_type_nid'])
            ->where('end_time', '>', $time)
            ->where(['status' => 1, 'type_id' => $imageId])
            ->orderBy('position', 'desc')
            ->get()->toArray();

        return $images ? $images : [];
    }
}