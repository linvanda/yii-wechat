<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/10/23
 * Time: 11:24
 */

namespace app\framework\weixin\qy\models\receive;

use app\framework\weixin\qy\models\tool\WXBizMsgCrypt;
use app\framework\weixin\qy\models\tool\XmlParse;

/**
 * 被动响应消息
 * Class ResponseMessage
 * @package app\framework\weixin\qy\models\receive
 */
class ResponseMessage
{
    private $_corp_id;
    private $_token;
    private $_encoding_aes_key;
    private $_msg;

    /**
     * 微信企业号
     * @param string $corp_id
     * @param string $token
     * @param string $encoding_aes_key
     * @param string|array|object $msg
     */
    public function __construct($corp_id, $token, $encoding_aes_key, $msg)
    {
        $this->_corp_id = $corp_id;
        $this->_token = $token;
        $this->_encoding_aes_key = $encoding_aes_key;
        $this->_msg = $msg;
    }

    /**
     * 获取消息
     * @return string
     */
    public function getMessage()
    {
        $msgCrypt = new WXBizMsgCrypt($this->_token, $this->_encoding_aes_key, $this->_corp_id);
        $sEncryptMsg = '';
        $msgCrypt->encryptMsg(XmlParse::toXml($this->_msg), null, null, $sEncryptMsg);
        return $sEncryptMsg;
    }
}
