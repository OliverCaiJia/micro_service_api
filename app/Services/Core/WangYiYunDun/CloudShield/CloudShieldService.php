<?php

namespace App\Services\Core\WangYiYunDun\CloudShield;

use App\Services\Core\WangYiYunDun\WangYiService;

class CloudShieldService extends WangYiService
{
    /**
     * 网易云盾(CloudShield)接口
     *
     * @param array $data
     * @return string
     */

    /**
     * 计算参数签名
     * $params 请求参数
     * $secretKey secretKey
     */
    public static function genSignature($secretKey, $params)
    {
        ksort($params);
        $buff = "";
        foreach ($params as $key => $value) {
            if ($value !== null) {
                $buff .= $key;
                $buff .= $value;
            }
        }
        $buff .= $secretKey;
        return md5($buff);
    }

    /**
     * 将输入数据的编码统一转换成utf8
     * @params 输入的参数
     */
    public static function toUtf8($params)
    {
        $utf8s = array();
        foreach ($params as $key => $value) {
            $utf8s[$key] = is_string($value) ? mb_convert_encoding($value, "utf8", self::INTERNAL_STRING_CHARSET) : $value;
        }
        return $utf8s;
    }

    /**
     * 图片安全请求接口简单封装
     * $params 请求参数
     */
    public static function check($params)
    {
        $params["secretId"] = self::SECRETID;
        $params["businessId"] = self::BUSINESSID;
        $params["version"] = self::VERSION;
        $params["timestamp"] = sprintf("%d", round(microtime(true) * 1000));// time in milliseconds
        $params["nonce"] = sprintf("%d", rand()); // random int

        $params = self::toUtf8($params);
        $params["signature"] = self::genSignature(self::SECRETKEY, $params);
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'timeout' => 10, // read timeout in seconds
                'content' => http_build_query($params),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents(self::API_URL, false, $context);
        if ($result === FALSE) {
            return array("code" => 500, "msg" => "file_get_contents failed.");
        } else {
            return json_decode($result, true);
        }
    }

    /**网易云盾过滤不合格图片
     * @param $DataUrl
     * @return int
     */
    public static function PhotoMain($DataUrl)
    {
        $images = array();
        array_push($images, array(// type=1表示传图片url检查
            "name" => $DataUrl,
            "type" => 1,
            "data" => $DataUrl,
        ));
        $params = array(
            "images" => json_encode($images),
            "account" => "",
            "ip" => ""
        );
        $ret = self::check($params);
        if ($ret["code"] == 200) {
            $result = $ret["result"];
            foreach ($result as $index => $image_ret) {
                $maxLevel = -1;
                foreach ($image_ret["labels"] as $index => $label) {
                    $maxLevel = $label["level"] > $maxLevel ? $label["level"] : $maxLevel;
                }
                if ($maxLevel == 0) {
                    return 0;//图片机器检测结果：最高等级为：正常
                } else if ($maxLevel == 1) {
                    return 1;//图片机器检测结果：最高等级为：嫌疑
                } else if ($maxLevel == 2) {
                    return 2;//图片机器检测结果：最高等级为：确定
                }
            }
        } else {
            echo 11;
        }
    }

    /**用户名安全
     * @param $params
     * @return array|mixed
     */
    public static function UserCheck($params)
    {
        $params["secretId"] = self::SECRETID;
        $params["businessId"] = self::TEXTBUSINESSID;
        $params["version"] = self::VERSION;
        $params["timestamp"] = sprintf("%d", round(microtime(true) * 1000));// time in milliseconds
        $params["nonce"] = sprintf("%d", rand()); // random int

        $params = self::toUtf8($params);
        $params["signature"] = self::genSignature(self::SECRETKEY, $params);
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'timeout' => 10, // read timeout in seconds
                'content' => http_build_query($params),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents(self::TEXT_URL, false, $context);
        if ($result === FALSE) {
            return array("code" => 500, "msg" => "file_get_contents failed.");
        } else {
            return json_decode($result, true);
        }
    }

    /**网易云盾用户名过滤
     * @param $id
     * @param $name
     */
    public static function UserMain($dataId, $id, $name)
    {
        $params = array(
            "dataId" => $dataId,
            "content" => $name,
            "dataType" => "1",
            "ip" => "",
            "account" => $id,
            "deviceType" => "4",
            "deviceId" => "",
            "callback" => $id,
            "publishTime" => round(microtime(true) * 1000)
        );
        $ret = self::UserCheck($params);
        if ($ret["code"] == 200) {
            $action = $ret["result"]["action"];
            $taskId = $ret["result"]["taskId"];
            $labelArray = $ret["result"]["labels"];
            if ($action == 0) {
                return $action;//文本机器检测结果：通过
            } else if ($action == 1) {
                return $action;//文本机器检测结果：嫌疑，需人工复审
//                return "taskId={$taskId}，，分类信息如下：" . json_encode($labelArray) . "\n";
            } else if ($action == 2) {
                return $action;//文本机器检测结果：不通过
//                return "taskId={$taskId}，，分类信息如下：" . json_encode($labelArray) . "\n";
            }
        } else {
            return $ret; // error handler
        }
    }


    /**评论内容安全
     * @param $params
     * @return array|mixed
     */
    public static function TextCheck($params)
    {
        $params["secretId"] = self::SECRETID;
        $params["businessId"] = self::REPLYBUSINESSID;
        $params["version"] = self::VERSION;
        $params["timestamp"] = sprintf("%d", round(microtime(true) * 1000));// time in milliseconds
        $params["nonce"] = sprintf("%d", rand()); // random int

        $params = self::toUtf8($params);
        $params["signature"] = self::genSignature(self::SECRETKEY, $params);
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'timeout' => 10, // read timeout in seconds
                'content' => http_build_query($params),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents(self::TEXT_URL, false, $context);
        if ($result === FALSE) {
            return array("code" => 500, "msg" => "file_get_contents failed.");
        } else {
            return json_decode($result, true);
        }
    }
    /**网易云盾评论过滤
     * @param $dataId
     * @param $id
     * @param $name
     * @return array|mixed
     */
    public static function ReplyMain($dataId, $id, $name)
    {
        $params = array(
            "dataId" => $dataId,
            "content" => $name,
            "dataType" => "1",
            "ip" => "",
            "account" => $id,
            "deviceType" => "4",
            "deviceId" => "",
            "callback" => $id,
            "publishTime" => round(microtime(true) * 1000)
        );
        $ret = self::TextCheck($params);
        if ($ret["code"] == 200) {
            $action = $ret["result"]["action"];
            $taskId = $ret["result"]["taskId"];
            $labelArray = $ret["result"]["labels"];
            if ($action == 0) {
                return $action;//文本机器检测结果：通过
            } else if ($action == 1) {
                return $action;//文本机器检测结果：嫌疑，需人工复审
//                return "taskId={$taskId}，，分类信息如下：" . json_encode($labelArray) . "\n";
            } else if ($action == 2) {
                return $action;//文本机器检测结果：不通过
//                return "taskId={$taskId}，，分类信息如下：" . json_encode($labelArray) . "\n";
            }
        } else {
            return $ret; // error handler
        }
    }
}