<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\component;

use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

/**
 * Description of WxComponent
 *
 * @author chenxy
 */
class WxComponent extends ApiBase
{
    private $_componentAppId;
    
    public function __construct($componentAppId = '')
    {
        $this->_componentAppId = $componentAppId?:self::getComponentAppId();
        if (!\Yii::$container->has('app\framework\weixin\component\IComponentAccessTokenRepository')) {
            throw new \app\framework\weixin\WeixinException('未注入app\framework\weixin\component\IComponentAccessTokenRepository实例');
        }

        $accessTokenRepository = \Yii::$container->get('app\framework\weixin\component\IComponentAccessTokenRepository');
        $accessTokenHelper = new \app\framework\weixin\component\ComponentAccessTokenHelper($this->_componentAppId, $accessTokenRepository);
        parent::__construct($accessTokenHelper);
    }
    
    public static function getComponentAppId()
    {
        $settingsAccessor = new \app\framework\settings\SettingsAccessor();
        $config = $settingsAccessor->get("wx_component_config");
        $config = json_decode($config);
        return $config->app_id;
    }
    
    /**
     * 获取第三方平台预授权码
     * @return object {
                "pre_auth_code":"Cx_Dk6qiBE0Dmx4EmlT3oRfArPvwSQ-oa3NL_fwHM7VI08r52wazoZX2Rhpz1dEw",
                "expires_in":600
                }
     */
    public function createPreauthcode()
    {
        $params =["component_appid" => $this->_componentAppId];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode', 'POST', "获取预授权码", $params);
        return $result;
    }
    
    /**
     * 获取公众号的授权信息
     * @param string $authCode 公众号授权码
     * @return object {
                    "authorization_info": {
                    "authorizer_appid": "wxf8b4f85f3a794e77",
                    "authorizer_access_token": "QXjUqNqfYVH0yBE1iI_7vuN_9gQbpjfK7hYwJ3P7xOa88a89-Aga5x1NMYJyB8G2yKt1KCl0nPC3W9GJzw0Zzq_dBxc8pxIGUNi_bFes0qM",
                    "expires_in": 7200,
                    "authorizer_refresh_token": "dTo-YCXPL4llX-u1W1pPpnp8Hgm4wpJtlR6iV0doKdY",
                    "func_info": [
                    {
                    "funcscope_category": {
                    "id": 1
                    }
                    },
                    {
                    "funcscope_category": {
                    "id": 2
                    }
                    },
                    {
                    "funcscope_category": {
                    "id": 3
                    }
                    }
                    ]
                    }
     */
    public function queryAuth($authCode)
    {
        $params =["component_appid" => $this->_componentAppId, "authorization_code" => $authCode];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/component/api_query_auth', 'POST', "获取公众号的授权信息", $params);
        return $result;
    }
    
    /**
     * 获取（刷新）授权公众号的令牌
     * @param string $authAppId 授权公众号id
     * @param string $authRefreshToken
     * @return object {
            "authorizer_access_token": "aaUl5s6kAByLwgV0BhXNuIFFUqfrR8vTATsoSHukcIGqJgrc4KmMJ-JlKoC_-NKCLBvuU1cWPv4vDcLN8Z0pn5I45mpATruU0b51hzeT1f8",
            "expires_in": 7200,
            "authorizer_refresh_token": "BstnRqgTJBXb9N2aJq6L5hzfJwP406tpfahQeLNxX0w"
            }
     */
    public function getAuthorizerToken($authAppId, $authRefreshToken)
    {
        $params =["component_appid" => $this->_componentAppId
                ,"authorizer_appid" => $authAppId
                ,"authorizer_refresh_token" => $authRefreshToken];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token', 'POST', "获取（刷新）授权公众号的令牌", $params);
        return $result;
    }
    
    /**
     * 获取授权方的账户信息
     * @param string $authAppId 授权公众号id
     * @return object {
                "authorizer_info": {
                "nick_name": "微信SDK Demo Special",
                "head_img": "http://wx.qlogo.cn/mmopen/GPyw0pGicibl5Eda4GmSSbTguhjg9LZjumHmVjybjiaQXnE9XrXEts6ny9Uv4Fk6hOScWRDibq1fI0WOkSaAjaecNTict3n6EjJaC/0",
                "service_type_info": { "id": 2 },
                "verify_type_info": { "id": 0 },
                "user_name":"gh_eb5e3a772040",
                "alias":"paytest01"
                },
                "qrcode_url":"URL",
                "authorization_info": {
                "appid": "wxf8b4f85f3a794e77",
                "func_info": [
                { "funcscope_category": { "id": 1 } },
                { "funcscope_category": { "id": 2 } },
                { "funcscope_category": { "id": 3 } }
                ]
                }
            }
     */
    public function getAuthorizerInfo($authAppId)
    {
        $params =["component_appid" => $this->_componentAppId
            ,"authorizer_appid" => $authAppId];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info', 'POST', "获取授权方的账户信息", $params);
        return $result;
    }
}
