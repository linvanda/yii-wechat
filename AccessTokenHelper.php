<?php

namespace app\framework\weixin;

/*
 * 获取accesstoken帮助类，支持企业号、服务号、订阅号，其中对于服务号和订阅号支持开发模式和授权模式
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use app\framework\weixin\interfaces\IAccessTokenRepository;

/**
 * Description of AccessTokenHelper
 *
 * @author Chenxy
 */
class AccessTokenHelper implements interfaces\IAccessTokenHelper
{
    /**
     * accessTokenRepository
     * @var \app\framework\weixin\IAccessTokenRepository
     */
    public $accessTokenRepository;
    
    /**
     * _wxInvoker
     * @var \app\framework\weixin\WxInvoker
     */
    protected $_wxInvoker;
    protected $_id;
    private $_isAuthAccess;
    
    /**
     * 构造方法
     * @param string $id 公众号对应的唯一标识符,支持account_id和original_id
     * @param \app\framework\weixin\IAccessTokenRepository $repository
     */
    public function __construct($id, IAccessTokenRepository $repository)
    {
        $this->accessTokenRepository = $repository;
        $this->_id = $id;
        $this->_isAuthAccess = ($repository->getConfigValue($id, 'is_authed') == 1);
        // 通过仓储获取当前调用接口的信息
        $this->_wxInvoker = $this->accessTokenRepository->getWxInvoker($id);
        // 增强健壮性
        if ($this->_isAuthAccess && empty($this->_wxInvoker->authRefreshToken)) {
            $authRefreshToken = $repository->getConfigValue($id, 'authorizer_refresh_token');
            $this->_wxInvoker->authRefreshToken = $authRefreshToken;
        }
    }
    
    /**
     * 获取access_token在url的参数名
     * @return string
     */
    public function getAccessTokenParamName()
    {
        // 官方文档说明（只是需将调用API时提供的公众号自身access_token参数，替换为authorizer_access_token）但实际不是
        return "access_token";
        //return $this->_isAuthAccess ? "authorizer_access_token" : "access_token";
    }
    
    /**
     * 吴中打通方案提供
     * @param type $forceExpire
     * @return type
     */
    public function accessToken($forceExpire = false)
    {
        if ($forceExpire) {
            // 不再提供强制过期功能
            //$this->makeExpire();
        }

        if ($this->isExpire()) {
            $this->freshAccessToken();
        }
        
        return ['access_token' => $this->_wxInvoker->accessToken
               ,'expire_time' => $this->_wxInvoker->expireTime];
    }
    
    /**
     * 获取access_token
     * @return string
     */
    public function getAccessToken()
    {
        if ($this->isExpire()) {
            $this->freshAccessToken();
        }
        return $this->_wxInvoker->accessToken;
    }
    
    /**
     * 设置access_token过期,无参时强制过期
     * @param int $errorCode
     */
    public function makeExpire($errorCode = -1)
    {
        if ($errorCode == -1 || $this->checkIsExpired($errorCode)) {
            $this->_wxInvoker->accessToken = '';
            $this->_wxInvoker->expireTime = 0;
            $this->accessTokenRepository->updateAccessToken($this->_id, '', null);
        }
    }
    
    /**
     * 通过接口获取access_token
     * @throws WeixinException
     */
    protected function freshAccessToken()
    {
        //如果获取锁失败，正常情况下是因为有其他进程正在刷新access_token，则此时直接使用原来的access_token（防止互刷）
        if (!$this->accessTokenLock()) {
            return;
        }

        $result = $this->_isAuthAccess
                ? $this->invokeAccesTokenByWechatAuth()
                : $this->invokeAccessTokenByAppSecert();

        // 获取accesstoken失败
        if (isset($result->errcode) and $result->errcode != 0) {
            //释放锁
            $this->accessTokenUnlock();
            throw new WeixinException('获取access_token失败，错误码：' . $result->errcode . '消息：' . $result->errmsg);
        }
        
        $accessToken = $this->_isAuthAccess ? $result->authorizer_access_token : $result->access_token;
        $authRefreshToken = $this->_isAuthAccess ? $result->authorizer_refresh_token : "";
        // 有效期－60
        $expireTime = time() + intval($result->expires_in) - 60;
        
        $this->_wxInvoker->accessToken = $accessToken;
        $this->_wxInvoker->expireTime = $expireTime;
        $this->_wxInvoker->authRefreshToken = $authRefreshToken;
        
        // 更新
        $this->accessTokenRepository->updateAccessToken($this->_id, $accessToken, $expireTime, $authRefreshToken);

        //释放锁
        $this->accessTokenUnlock();
    }
    
    /**
     * 判断access_token是否已过期
     * @return bool
     */
    protected function isExpire()
    {
        return empty($this->_wxInvoker)
                || empty($this->_wxInvoker->accessToken)
                || empty($this->_wxInvoker->expireTime)
                || time() >= $this->_wxInvoker->expireTime;
    }

    /**
     * 同时只能有一个进程（客户端）刷新access_token，否则会出现相互覆盖，而且有可能导致过期时间内所有人都无法使用正确的access_token
     * @return bool 是否锁定成功
     */
    private function accessTokenLock()
    {
        if (!\Yii::$app->cache->exists($this->accessTokenLockKey())) {
            return \Yii::$app->cache->set($this->accessTokenLockKey(), 1, 5);
        }

        return false;
    }

    /**
     * 释放锁
     * @return void
     */
    private function accessTokenUnlock()
    {
        if (\Yii::$app->cache->exists($this->accessTokenLockKey())) {
            \Yii::$app->cache->delete($this->accessTokenLockKey());
        }
    }

    private function accessTokenLockKey()
    {
        $tenantCode = '';
        if (isset(\Yii::$app->context) and isset(\Yii::$app->context->tenantCode)) {
            $tenantCode = \Yii::$app->context->tenantCode;
        }
        return 'access_token_lock_'.$tenantCode.'_'.$this->_id;
    }
    
    private function invokeAccesTokenByWechatAuth()
    {
        $apiProxy = new proxy\component\WxComponent();
        $appId = $this->_wxInvoker->appId;

        try {
            $result = $apiProxy->getAuthorizerToken($appId, $this->_wxInvoker->authRefreshToken);
        } catch (WeixinException $e) {
            if (strpos($e->getMessage(), 'refresh_token is invalid') !== false) {
                //refresh_token缓存出问题，从数据库直接查下
                $this->_wxInvoker = $this->accessTokenRepository->getWxInvoker($appId, false);
                $result = $apiProxy->getAuthorizerToken($appId, $this->_wxInvoker->authRefreshToken);
            }
        }

        return $result;
    }
    
    private function invokeAccessTokenByAppSecert()
    {
        $restClient = new \app\framework\webService\RestClientHelper();
        $apiUrl = $this->_wxInvoker->buildGetTokenUrl();
        $result = $restClient->invoke($apiUrl, []);
        return $result;
    }
    
    /**
     * 根据错误码判断access_token是否过期
     * @param int $errorCode
     * @return bool
     */
    public function checkIsExpired($errorCode)
    {
        return ($errorCode == 42001 || $errorCode == 40001 || $errorCode == 40014);
    }
    
    /**
     * 写接口调用日志
     * @param type $method
     * @param type $invokeUrl
     * @param type $invokeTime
     * @param type $parameter
     * @throws \Exception
     */
    public function writeLog($method, $invokeUrl, $invokeTime, $parameter)
    {
        try {
            $accountId = $this->_accessTokenRepository->getConfigValue($this->_id, 'id');
        } catch (\Exception $ex) {
            $accountId = $this->_id;
        }
        
        $logRow = [
            'id' => \app\framework\utils\StringHelper::uuid(),
            'account_id' => $accountId,
            'method' => $method,
            'invoke_url' => $invokeUrl,
            'invoke_time' => $invokeTime,
            'parameter' => $parameter
        ];
        
        $tenantCode = "";
        if ($_GET["appid"]) {
            $tenantCode = \app\framework\weixin\helper\BizTenantCodeHelper::getTenantCodeByAppId($appId);
        }
        
        if (!$tenantCode) {
            $tenantReader= \Yii::$container->get('app\framework\biz\tenant\TenantReaderInterface');
            $tenantCode = $tenantReader->getCurrentTenantCode();
        }
        $dbConn = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn($tenantCode);
        $dbConn->createCommand()->insert('p_wechat_api_log', $logRow)->execute();
    }
    
    /**
     * 获取唯一标识
     * @return string
     */
    public function getId()
    {
        // 保证兼容构建函数各种id
        return $this->accessTokenRepository->getConfigValue($this->_id, 'id');
    }
    
    /**
     * 获取微信appid
     * @return string
     */
    public function getAppId()
    {
        if ($this->_wxInvoker && $this->_wxInvoker->appId) {
            return $this->_wxInvoker->appId;
        }
        
        // 从配置中读取
        return $this->accessTokenRepository->getConfigValue($this->_id, 'app_id');
    }
}
