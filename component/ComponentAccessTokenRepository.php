<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\component;

use app\framework\weixin\WeixinException;

/**
 * 微信第三方开放平台ComponentAccessTokenRepository仓储
 *
 * @author chenxy
 */
class ComponentAccessTokenRepository implements \app\framework\weixin\interfaces\IComponentAccessTokenRepository
{
    const WECHAT_COMPONENT_TICKET_PREFIX = "wechat_component_ticket_";

    /**
     * 获取wxInvoker
     * @param string $appId 第三方平台appid
     * @return \app\framework\weixin\WxInvoker
     */
    public function getWxInvoker($appId, $useCache = true)
    {
        // 从数据缓存中读取wxInvoke数据
        if ($useCache) {
            $cache = \Yii::$app->cache;
            $cacheKey = $this->getWxInvokerCacheKey($appId);
            if ($cache->exists($cacheKey)) {
                return $cache[$cacheKey];
            }
        }
        
        // 从数据库表中构造
        return $this->getWxInvokerByConfig($appId);
    }
    
    public function updateAccessToken($appId, $accessToken, $expireTime)
    {
         // 更新到数据缓存
        $wxInvoker = $this->getWxInvoker($appId);
        $wxInvoker->accessToken = $accessToken;
        $wxInvoker->expireTime = $expireTime;
        $cache = \Yii::$app->cache;
        $cacheKey = $this->getWxInvokerCacheKey($appId);
        $cache[$cacheKey] = $wxInvoker;
    }
    
    public function getConfigValue($appId, $configKey)
    {
        $wxConfig = $this->getSecretConfig($appId);
        if (!array_key_exists($configKey, $wxConfig)) {
            throw new WeixinException("找不到配置项{$configKey}");
        }
        
        return $wxConfig[$configKey];
    }
    
    public function updateVerifyTicket($appId, $ticket)
    {
        $cacheKey = static::WECHAT_COMPONENT_TICKET_PREFIX . $appId;
        $cache = \Yii::$app->cache;
        $cache[$cacheKey] = $ticket;
        $this->updateConfigValue($appId, "ticket", $ticket);
        /* 文件存储
        $filename = $this->getTicketFilePath($appId);
        $handle = fopen($filename, "w");
        fwrite($handle, json_encode(['verify_ticket' => $ticket]));
        fclose($handle);
         */
    }
    
    public function getVerifyTicket($appId)
    {
        $cacheKey = static::WECHAT_COMPONENT_TICKET_PREFIX . $appId;
        $cache = \Yii::$app->cache;
        if ($cache->exists($cacheKey)) {
            return $cache[$cacheKey];
        }
        
        $settingsAccessor = new \app\framework\settings\SettingsAccessor();
        $config = $settingsAccessor->get("wx_component_config");
        $config = json_decode($config);
        return ($config->app_id == $appId) ? $config->ticket : "";
        /* 文件存储
        $filename = $this->getTicketFilePath($appId);
        if (file_exists($filename)) {
            $handle = fopen($filename, "r");
            $contents = fread($handle, filesize($filename));
            fclose($handle);
            $data = json_decode($contents);
            return $data->verify_ticket;
        }
        return '';
        */
    }

    public function updateConfigValue($appId, $configKey, $configValue)
    {
        $settingsAccessor = new \app\framework\settings\SettingsAccessor();
        $config = $settingsAccessor->get("wx_component_config");
        $config = json_decode($config);
        $config->$configKey = $configValue;
        $newConfig = json_encode($config, JSON_UNESCAPED_UNICODE);
        $settingsAccessor->set("wx_component_config", $newConfig, "微信第三方开放平台配置");
    }
    
    private function getTicketFilePath($appId)
    {
        return $_SERVER ['DOCUMENT_ROOT'] . "/temp/component_verify_ticket_{$appId}.cache";
    }

    private function getWxInvokerByConfig($appId)
    {
        $wxInvokeConfig =$this->getSecretConfig($appId);
        if ($wxInvokeConfig === false) {
            throw new \app\framework\weixin\WeixinException("未找到appid:{$appid}的微信第三方开放平台配置");
        }
        
        $wxInvoker = new \app\framework\weixin\WxInvoker();
        $wxInvoker->appId = $wxInvokeConfig['app_id'];
        $wxInvoker->appSecret = $wxInvokeConfig['app_secret'];
        $wxInvoker->appType = "开放平台";
        $wxInvoker->token = $wxInvokeConfig["token"];
        $wxInvoker->encodingKey = $wxInvokeConfig["encoding_key"];
        return $wxInvoker;
    }
    
    private function getSecretConfig($appId)
    {
        $settingAccessor = new \app\framework\settings\SettingsAccessor();
        $config = $settingAccessor->get("wx_component_config");
        $config = json_decode($config);
        return ($config->app_id == $appId) ? (array)$config : [];
    }
    
    private function getWxInvokerCacheKey($appId)
    {
        return "wechat_component_invoker_$appId";
    }

    public static function clearCache($appId)
    {
        $cache = \Yii::$app->cache;
        $self = new self();
        $cacheKey = $self->getWxInvokerCacheKey($appId);
        $cache->exists($cacheKey) && $cache->delete($cacheKey);
    }
}
