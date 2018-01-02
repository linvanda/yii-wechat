<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\component;

use app\framework\weixin\AccessTokenHelper;
use app\framework\weixin\interfaces\IComponentAccessTokenRepository;
use app\framework\weixin\proxy\component\WxComponent;
use app\framework\weixin\WeixinException;

/**
 * Description of ComponentAccessTokenHelper
 *
 * @author chenxy
 */
class ComponentAccessTokenHelper extends AccessTokenHelper
{
    const API_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
    
    public function __construct($componentAppId, IComponentAccessTokenRepository $repository)
    {
        $this->accessTokenRepository = $repository;
        $this->_id = $componentAppId;
        // 通过仓储获取当前调用接口的信息
        $this->_wxInvoker = $this->accessTokenRepository->getWxInvoker($componentAppId);
    }

    public function getAccessTokenParamName()
    {
        return "component_access_token";
    }

    protected function freshAccessToken()
    {
        // 获取企业开发者访问api所需数据
        $appId = WxComponent::getComponentAppId();
        $ticket = $this->accessTokenRepository->getVerifyTicket($appId);
        $restClient = new \app\framework\webService\RestClientHelper();
        $params = ["component_appid" => $this->_wxInvoker->appId ,
                    "component_appsecret" => $this->_wxInvoker->appSecret,
                    "component_verify_ticket" => $ticket ];
        $result = $restClient->invoke(self::API_ACCESS_TOKEN_URL, $params, 'POST');
        
        // 获取accesstoken失败
        if (isset($result->errcode)) {
            throw new WeixinException('获取component_access_token失败，错误码：' . $result->errcode . '消息：' . $result->errmsg . "数据包:" . json_encode($params));
        }
        
        $accessToken = $result->component_access_token;
        
        // 设定过期时间
        $expireTime = time() + intval($result->expires_in) - 60;
        $this->_wxInvoker->accessToken = $accessToken;
        $this->_wxInvoker->expireTime = $expireTime;
        
        // 更新
        $this->accessTokenRepository->updateAccessToken($this->_id, $accessToken, $expireTime);
    }
}
