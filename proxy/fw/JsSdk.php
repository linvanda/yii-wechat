<?php

namespace app\framework\weixin\proxy\fw;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 获取微信用户相关接口
 *
 * @author Chenxy
 */
use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

class JsSdk extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
   
    public function getTicket($type = 'jsapi')
    {
        return $this->execute("https://api.weixin.qq.com/cgi-bin/ticket/getticket?type={$type}", "GET", "获取jssdk-ticket");
    }
    
    /**
     * 获取jsapiticket
     * @return string
     */
    public function getJsApiTicket()
    {
        // 从缓存取
        $keyId = $this->_accessTokenHelper->getId();
        $cacheKey = "wechat_jssdk_ticket_{$keyId}";
        $ticket = \Yii::$app->cache->get($cacheKey);
        if ($ticket) {
            return $ticket;
        }
        
        // 从接口获取
        $result = $this->getTicket('jsapi');
        \Yii::$app->cache->get($cacheKey, $result->ticket, $result->expires_in - 60);
        return $result->ticket;
    }
    
    /**
     * 获取jssdk签名包
     * @param string $url 默认对当前请求url进行签名
     * @return array ["appId" => 公众号id,
            "nonceStr" => 随机数,
            "timestamp" => 时间戳,
            "url" => url,
            "signature" => 签名,
            "rawString" => 真实的签名字符串，可用于调试
        ]
     */
    public function getSignPackage($url = '')
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $url ?: "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $jsapiTicket = $this->getJsApiTicket();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = ["appId" => $this->_accessTokenHelper->getAppId(),
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        ];
        
        return $signPackage; 
    }
    
    /**
     * 生成一个16位的随时字符串
     * @return type
     */
    private function createNonceStr()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < 16; $i++) {
          $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}
