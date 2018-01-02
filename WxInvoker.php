<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin;

/**
 * Description of WxInvoke
 *
 * @author Chenxy
 */
class WxInvoker
{
    /**
     * 对应微信企业号的corpId,公众号的appid
     * @var string
     */
    public $appId;
    
    /**
     * 对应微信企业号的corpsecret,公众号的secret
     * @var string
     */
    public $appSecret;
    
    /**
     * 访问微信api的接口凭证
     * @var string
     */
    public $accessToken;
    
    /**
     * access_token过期时间戳
     * @var int
     */
    public $expireTime;
    
    /**
     * 枚举：企业号\公众号\订阅号
     * string appType
     */
    public $appType;

    /**
     * 微信支付商户号
     * @var string
     */
    public $mchId;
    
    /**
     * 微信支付密钥
     * @var string
     */
    public $mchKey;
    
    /**
     * 微信支付接口API证书
     * @var string
     */
    public $mchSSLCert;
    
    /**
     * 微信支付接口API密钥
     * @var string
     */
    public $mchSSLKey;

    /**
     * 公众号唯一标识
     * @var string
     */
    public $originalId;

    /**
     * 商户名称
     * @var string
     */
    public $mchName;
    
    /**
     * 签名钥匙
     * @var string
     */
    public $token;
    
    /**
     * 消息加密钥
     * @var string
     */
    public $encodingKey;
    
    /**
     * 授权刷新令牌
     * @var string
     */
    public $authRefreshToken;

    /**
     * 根据不号的类型构造获取access_token的url
     * @param string $ 枚举：企业号\服务号\订阅号
     * @return string 获取access_token的url
     * @throws \InvalidArgumentException
     */
    public function buildGetTokenUrl()
    {
        $appType = $this->appType;
        $appId = $this->appId;
        $appSecret = $this->appSecret;
        switch ($appType) {
            case '企业号':
                $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$appId&corpsecret=$appSecret";
                break;
            case '服务号':
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
                break;
            case '订阅号':
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
                break;
            default :
                throw new \InvalidArgumentException("参数值appType:{$appType}无效,允许的值：企业号、服务号、订阅号");
        }
        
        return $url;
    }
}
