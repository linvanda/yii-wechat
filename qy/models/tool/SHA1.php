<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * User: wangl10
 * Date: 2015/7/31
 * Time: 17:50
 */

namespace app\framework\weixin\qy\models\tool;

/**
 * Class SHA1
 * @package app\modules\wx\services\wx_tool
 */
class SHA1
{
    /**
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt_msg 密文消息
     * @return array
     */
    public static function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        try {
            $array = [$encrypt_msg, $token, $timestamp, $nonce];
            //排序
            sort($array, SORT_STRING);
            $str = implode($array);
            return [ErrorCode::$OK, sha1($str)];
        } catch (\Exception $e) {
            return [ErrorCode::$ComputeSignatureError, null];
        }
    }
}
