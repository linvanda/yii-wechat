<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\framework\weixin;

use app\framework\weixin\interfaces\IAccessTokenRepository;
use app\framework\weixin\WeixinException;
use app\framework\weixin\WxInvoker;

/**
 * Token仓储实现，用于获取access_token
 *
 * @author Chenxy
 */
class DbAccessTokenRepository implements IAccessTokenRepository
{
    const ACCOUNT_CONFIG_PREFIX = "wechat_account_config_";

    /**
     * 读取微信调用信息
     * @param string $id 微信号 appid
     * @return \app\framework\weixin\WxInvoker
     */
    public function getWxInvoker($id, $useCache = true)
    {
        // 从数据缓存中读取wxInvoke数据
        if ($useCache) {
            $cache = \Yii::$app->cache;
            $cacheKey = $this->getWxInvokerCacheKey($id);
            if ($cache->exists($cacheKey)) {
                return $cache[$cacheKey];
            }
        }

        // 从数据库表中构造
        return $this->getWxInvokerByConfig($id);
    }

    /**
     * 更新access_token
     * @param string $id 微信号
     * @param string $accessToken access_token
     * @param datetime $expireTime 过期时间
     * @param string $authRefreshToken 授权刷新令牌
     */
    public function updateAccessToken($id, $accessToken, $expireTime, $authRefreshToken = '')
    {
        // 更新到数据缓存
        $wxInvoker = $this->getWxInvoker($id);
        $wxInvoker->accessToken = $accessToken;
        $wxInvoker->expireTime = $expireTime;
        $wxInvoker->authRefreshToken = $authRefreshToken;
        $cache = \Yii::$app->cache;
        $cacheKey = $this->getWxInvokerCacheKey($id);
        $cache[$cacheKey] = $wxInvoker;

        // 更新刷新令牌避免清缓存丢失需要重新授权
        if ($authRefreshToken) {
            $this->updateConfigValue($id, "authorizer_refresh_token", $authRefreshToken);
        }
    }

    /**
     * 从仓储中读取指定键的值
     * @param string $id 微信号
     * @param string $configKey 配置键
     * @throws WeixinException
     */
    public function getConfigValue($id, $configKey)
    {
        $wxConfig = $this->getSecretConfig($id);
        if (!array_key_exists($configKey, $wxConfig)) {
            throw new WeixinException("表p_account未找到{$configKey}字段");
        }

        return $wxConfig[$configKey];
    }

    public function updateConfigValue($id, $configKey, $configValue)
    {
        $wxConfig = $this->getSecretConfig($id);
        if (!array_key_exists($configKey, $wxConfig)) {
            throw new WeixinException("表p_account未找到{$configKey}字段");
        }

        // 更新字段
        $tenantCode = $this->getTenantCode($id);
        $dbRouting = \Yii::$container->get('app\framework\biz\db\DbRoutingInterface');
        $dbConn = $dbRouting->getTenantDb($tenantCode);
        $dbConn->createCommand()->update(
            'p_account',
            [$configKey => $configValue],
            "(original_id=:id or id=:id or app_id=:id) and is_deleted=0",
            [':id' => $id]
        )->execute();

        // 清除缓存,刷新令牌不清缓存否则对性能有影响
        if (!in_array($configKey, ['authorizer_refresh_token'])) {
            $this->internalClearCache($id);
        }
    }

    /**
     * 通过读取数据库初始化一个wxInvoker对象
     * @param type $id 微信号
     * @return \app\framework\weixin\WxInvoker
     * @throws WeixinException
     */
    private function getWxInvokerByConfig($id)
    {
        $wxInvokeConfig = $this->getSecretConfig($id);
        $wxInvoker = new WxInvoker();
        if (empty($wxInvokeConfig['app_id']) || empty($wxInvokeConfig['type'])) {
            throw new exceptions\AccountNotFoundException("微信号:{$id},表p_account未配置appId或type");
        }

        if ($wxInvokeConfig['is_authed'] == 0 && empty($wxInvokeConfig['app_secret'])) {
            throw new exceptions\AccountNotFoundException("微信号:{$id},表p_account未配置appSecret");
        }

        $wxInvoker->appId = $wxInvokeConfig['app_id'];
        $wxInvoker->appSecret = $wxInvokeConfig['app_secret'];
        $wxInvoker->appType = $wxInvokeConfig['type'];
        $wxInvoker->mchId = $wxInvokeConfig["mch_id"];
        $wxInvoker->mchKey = $wxInvokeConfig["mch_key"];
        $wxInvoker->mchSSLCert = $wxInvokeConfig["mch_ssl_cert"];
        $wxInvoker->mchSSLKey = $wxInvokeConfig["mch_ssl_key"];
        $wxInvoker->mchName = $wxInvokeConfig["name"];
        $wxInvoker->originalId = $wxInvokeConfig['original_id'];
        $wxInvoker->authRefreshToken = $wxInvokeConfig['authorizer_refresh_token'];
        return $wxInvoker;
    }

    /**
     * 获取租户代码
     * @param string $id
     * @return type
     * @throws \Exception
     */
    protected function getTenantCode($id)
    {
        // 优先取函数参数中
        $appId = isset($_GET["appid"]) ? $_GET["appid"] : '';
        $appId = ($id && strpos($id, "wx") === 0) ? $id : $appId;
        $tenantCode = "";
        if ($appId) {
            $tenantCode = \app\framework\weixin\helper\BizTenantCodeHelper::getTenantCodeByAppId($appId);
        }

        if ($tenantCode) {
            return $tenantCode;
        }

        // 从路径中读取
        if (!\Yii::$container->has('app\framework\biz\tenant\TenantReaderInterface')) {
            throw new \Exception('未注入app\framework\biz\tenant\TenantReaderInterface实例');
        }
        $tenantReader = \Yii::$container->get('app\framework\biz\tenant\TenantReaderInterface');
        // 读取企业代码
        return $tenantReader->getCurrentTenantCode();
    }

    /**
     * 读取公众号安全设置
     * @param type $id
     * @return type
     */
    private function getSecretConfig($id)
    {
        $config = $this->getAccountConfigFromDB($id);

        if ($config === false) {
            throw new WeixinException("{$id}找不到公众号数据");
        }

        return $config;
    }

    private function getAccountConfigFromDB($id)
    {
        // 获取数据库连接
        $tenantCode = $this->getTenantCode($id);
        $dbRouting = \Yii::$container->get('app\framework\biz\db\DbRoutingInterface');
        $dbConn = $dbRouting->getTenantDb($tenantCode);
        $sql = "select id,name,original_id,wechat_number,type,app_id,app_secret"
            . ",mch_id,mch_key,corp_id,token,mch_ssl_cert, mch_ssl_key, partner_event_url"
            . ",is_authed, authorizer_refresh_token,unauth_time,auth_time,authorized_privilege_set,authorizer_code,partner_secret_key "
            . "from p_account "
            . "where (original_id=:id or id=:id or app_id=:id) and is_deleted=0";
        $config = $dbConn->createCommand($sql, [":id"=>$id])->queryOne();

        return $config;
    }

    /**
     * 统一缓存key,key为微信公众号原始id或appid
     * @param string $id 兼容公众号id和微信原始id或appid
     * @return string 缓存唯一标识
     */
    private function getWxInvokerCacheKey($id)
    {
        $regex = "/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/";
        // 原始id或appid
        if (!preg_match($regex, $id)) {
            $id = $this->getConfigValue($id, "id");
        }
        return "wechat_invoker_{$id}";
    }

    private function getWechatInfoCacheKey($id)
    {
        return static::ACCOUNT_CONFIG_PREFIX . $id;
    }


    private function internalClearCache($id)
    {
        $cache = \Yii::$app->cache;
        $cacheKey = $this->getWxInvokerCacheKey($id);
        $cache->exists($cacheKey) && $cache->delete($cacheKey);
        $config = $this->getAccountConfigFromDB($id);
        $config && $cache->exists(static::ACCOUNT_CONFIG_PREFIX . $config["id"]) && $cache->delete(static::ACCOUNT_CONFIG_PREFIX . $config["id"]);
        $config && $cache->exists(static::ACCOUNT_CONFIG_PREFIX . $config["app_id"]) && $cache->delete(static::ACCOUNT_CONFIG_PREFIX . $config["app_id"]);
        $config && $cache->exists(static::ACCOUNT_CONFIG_PREFIX . $config["original_id"]) && $cache->delete(static::ACCOUNT_CONFIG_PREFIX . $config["original_id"]);
    }

    public static function clearCache($id)
    {
        $self = new self();
        $self->internalClearCache($id);
    }
}
