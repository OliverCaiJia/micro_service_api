<?php

namespace App\Constants;

/**
 * 简易枚举实现
 */
abstract class AppConstant
{

    private static $constant;

    /**
     * 枚举元素的数组。
     *
     * @var array
     */
    protected $FValues = [];

    /**
     * 添加元素
     *
     * @param integer $Element

     */
    protected function add($Element)
    {
        $this->FValues[$Element] = $Element;
    }

    /**
     * 进行初始化，定义枚举范围。
     *
     */
    protected function doInit()
    {
        foreach (get_object_vars($this) as $value) {
            $this->add($value);
        }
    }

    /**
     * 选择枚举元素。
     *
     * @param integer $Element 元素名称
     * @return integer 元素值
     */
    public static function get($Element)
    {
        if (!(self::$constant instanceof static))
        {
            self::$constant = new static();
        }

        if (array_key_exists($Element, self::$constant->FValues))
        {
            return (self::$constant->FValues[$Element]);
        }
    }

    function __construct()
    {
        $this->doInit();
    }

}
