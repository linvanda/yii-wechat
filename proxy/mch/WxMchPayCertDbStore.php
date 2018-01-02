<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\mch;

/**
 * Description of WxMchPayCertDbStore
 *
 * @author chenxy
 */
class WxMchPayCertDbStore implements \app\framework\weixin\interfaces\IWxMchPayCertStore
{
    private $_encryptKey;
    
    private $_wxMchInfo;
    
    private $_mchId;
    
    private $_wechatId;
    
    public function __construct($wechatId)
    {
        $this->_wechatId = $wechatId;
        $this->initWxMchConfig();
    }
    
    public function getSSLCert()
    {
        // 解密内容
        $conent = $this->_wxMchInfo->mchSSLCert;
        $conent = base64_decode($conent);
        if ($conent === false) {
            return false;
        }
        $certString = \Yii::$app->getSecurity()->decryptByKey($conent, $this->_encryptKey);
        return $certString;
    }
    
    public function getSSLKey()
    {
        // 解密内容
        $conent = $this->_wxMchInfo->mchSSLKey;
        $conent = base64_decode($conent);
        if ($conent === false) {
            return false;
        }
        $keyString = \Yii::$app->getSecurity()->decryptByKey($conent, $this->_encryptKey);
        return $keyString;
    }
    
    private function initWxMchConfig()
    {
        $accessTokenRepository = \Yii::$container->get('app\framework\weixin\interfaces\IAccessTokenRepository');
        $this->_wxMchInfo = $accessTokenRepository->getWxInvoker($this->_wechatId);
        $this->_mchId = $this->_wxMchInfo->mchId;
        $this->_encryptKey = substr($this->_wxMchInfo->mchKey, 0, 16);
    }
}
