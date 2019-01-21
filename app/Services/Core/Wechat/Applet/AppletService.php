<?php

namespace App\Services\Core\Wechat\Applet;

use App\Services\AppService;

define("TOKEN", "applet");

class AppletService extends AppService
{
    public static $util;      // 单例对象

    /** 单例构造
     * @return static
     */
    public static function i()
    {
        if (!(self::$util instanceof static)) {
            self::$util = new static();
        }

        return self::$util;
    }

    //验证消息
    public function valid($data = [])
    {
        $echoStr = $data['echostr'];
        if ($this->checkSignature($data)) {
            echo $echoStr;
            exit;
        }
    }

    //检查签名
    private function checkSignature($data = [])
    {
        $signature = $data["signature"];
        $timestamp = $data["timestamp"];
        $nonce = $data["nonce"];
        $token = 'applet';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $result = $this->receiveText($postObj);
            echo $result;
        } else {
            echo "qqq";
            exit;
        }
    }

    //接收文本消息
    private function receiveText($object)
    {
        $content = date("Y-m-d H:i:s", time());
        $result = $this->transmitText($object, $content);

        return $result;
    }

    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }
}