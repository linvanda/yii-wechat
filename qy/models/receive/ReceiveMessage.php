<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/10/22
 * Time: 17:39
 */
namespace app\framework\weixin\qy\models\receive;

use app\framework\weixin\qy\models\tool\WXBizMsgCrypt;
use app\framework\weixin\qy\models\tool\XmlParse;

/**
 * 接收企业消息
 * Class ReceiveMessage
 * @package app\framework\weixin\qy\models\receive
 */
class ReceiveMessage
{
    private $_corp_id;
    private $_token;
    private $_encoding_aes_key;

    /**
     * 微信企业号
     * @param string $corp_id
     * @param string $token
     * @param string $encoding_aes_key
     */
    public function __construct($corp_id, $token, $encoding_aes_key)
    {
        $this->_corp_id = $corp_id;
        $this->_token = $token;
        $this->_encoding_aes_key = $encoding_aes_key;
    }

    /**
     * 获取消息
     * @return array|null|string
     */
    public function getMessage()
    {
        return $this->resolveMessage();
    }

    /**
     * 获取明文消息
     * @return array|null|string
     */
    private function resolveMessage()
    {
        $msg_signature = $_GET['msg_signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $echo_str = $_GET['echostr'];
        $msg_body = file_get_contents('php://input');//xml中包含加密的信息

        if (empty($msg_signature) || empty($timestamp) || empty($nonce) || empty($this->_token) || empty($this->_encoding_aes_key)) {
            return null;
        }

        $msgCrypt = new WXBizMsgCrypt($this->_token, $this->_encoding_aes_key, $this->_corp_id);
        if (!empty($echo_str)) {
            $verifySuccessStr = '';
            //验证echo_str
            $verifyCode = $msgCrypt->VerifyURL($msg_signature, $timestamp, $nonce, $echo_str, $verifySuccessStr);
            if ($verifyCode !== 0 || empty($verifySuccessStr)) {
                return null;
            }
            return ['echo_str' => $verifySuccessStr];
        }

        if (empty($msg_body)) {
            return null;
        }
        $msg = XmlParse::extract($msg_body);
        if ($msg[0] !== 0) {
            return null;
        }
        $corpId = $msg[2];//企业号Id
        $encrypted = $msg[1];//加密后的消息
        if (empty($corpId) || empty($encrypted) || strcasecmp($corpId, $this->_corp_id) !== 0) {
            return null;
        }

        $xmlMsg = '';
        //解密消息
        $verifyCode = $msgCrypt->decryptMsg($msg_signature, $timestamp, $nonce, $msg_body, $xmlMsg);
        if ($verifyCode !== 0 || empty($xmlMsg)) {
            return null;
        }
        try {
            $msg = XmlParse::toArray($xmlMsg);
            return $msg;
        } catch (\Exception $e) {
            return null;
        }
    }
}
