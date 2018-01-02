<?php

/**
 * Created by wangl10@mysoft.com.cn.
 * User: wangl10
 * Date: 2015/7/31
 * Time: 17:39
 */

namespace app\framework\weixin\qy\models\tool;

use app\framework\weixin\qy\models\tool\PrpCrypt as PrpCryptTool;

class WXBizMsgCrypt
{
    private $_token;
    private $_encodingAesKey;
    private $_corpId;

    /**
     * @param $token
     * @param $encodingAesKey
     * @param $corpId
     */
    public function __construct($token, $encodingAesKey, $corpId)
    {
        $this->_token = $token;
        $this->_encodingAesKey = $encodingAesKey;
        $this->_corpId = $corpId;
    }

    /**
     * 验证URL
     * @param $sMsgSignature
     * @param $sTimeStamp
     * @param $sNonce
     * @param $sEchoStr
     * @param $sReplyEchoStr
     * @return int
     */
    public function verifyURL($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr, &$sReplyEchoStr)
    {
        if (strlen($this->_encodingAesKey) != 43) {
            return ErrorCode::$IllegalAesKey;
        }

        $pc = new PrpCryptTool($this->_encodingAesKey);
        //verify msg_signature
        $array = SHA1::getSHA1($this->_token, $sTimeStamp, $sNonce, $sEchoStr);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }

        $signature = $array[1];
        if ($signature != $sMsgSignature) {
            return ErrorCode::$ValidateSignatureError;
        }

        $result = $pc->decrypt($sEchoStr, $this->_corpId);
        if ($result[0] != 0) {
            return $result[0];
        }
        $sReplyEchoStr = $result[1];

        return ErrorCode::$OK;
    }

    /**
     * 加密消息
     * @param $sReplyMsg
     * @param $sTimeStamp
     * @param $sNonce
     * @param $sEncryptMsg
     * @return int
     */
    public function encryptMsg($sReplyMsg, $sTimeStamp, $sNonce, &$sEncryptMsg)
    {
        $pc = new PrpCryptTool($this->_encodingAesKey);

        //加密
        $array = $pc->encrypt($sReplyMsg, $this->_corpId);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }

        if ($sTimeStamp == null) {
            $sTimeStamp = time();
        }
        if ($sNonce == null) {
            $sNonce = $pc->getRandomStr();
        }
        $encrypt = $array[1];

        //生成安全签名
        $array = SHA1::getSHA1($this->_token, $sTimeStamp, $sNonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];

        //生成发送的xml
        $sEncryptMsg = XMLParse::generate($encrypt, $signature, $sTimeStamp, $sNonce);
        return ErrorCode::$OK;
    }

    /**
     * 解密消息
     * @param $sMsgSignature
     * @param null $sTimeStamp
     * @param $sNonce
     * @param $sPostData
     * @param $sMsg
     * @return int
     */
    public function decryptMsg($sMsgSignature, $sTimeStamp, $sNonce, $sPostData, &$sMsg)
    {
        if (strlen($this->_encodingAesKey) != 43) {
            return ErrorCode::$IllegalAesKey;
        }

        $pc = new PrpCryptTool($this->_encodingAesKey);

        //提取密文
        $array = XMLParse::extract($sPostData);
        $ret = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        if (!isset($sTimeStamp)) {
            $sTimeStamp = time();
        }

        $encrypt = $array[1];
        //$touser_name = $array[2]; 未使用

        //验证安全签名
        $array = SHA1::getSHA1($this->_token, $sTimeStamp, $sNonce, $encrypt);
        $ret = $array[0];

        if ($ret != 0) {
            return $ret;
        }

        $signature = $array[1];
        if ($signature != $sMsgSignature) {
            return ErrorCode::$ValidateSignatureError;
        }

        $result = $pc->decrypt($encrypt, $this->_corpId);
        if ($result[0] != 0) {
            return $result[0];
        }
        $sMsg = $result[1];

        return ErrorCode::$OK;
    }
}
