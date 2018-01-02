<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin;

/**
 * Description of AccessTokenRepository
 *
 * @author Chenxy
 */
class AccessTokenRepository implements interfaces\IAccessTokenRepository
{
    /**
     * 读取微信调用信息
     * @param string $id access_token对应的唯一标识符,每个公众号\公众号\订阅号一个
     * @return \app\framework\weixin\WxInvoker
     */
    public function getWxInvoker($id, $useCache = true)
    {
        // 从数据缓存中读取wxInvoke数据
        if ($useCache) {
            $cache = \Yii::$app->cache;
            $cacheKey = self::getWxInvokerCacheKey($id);
            if ($cache->exists($cacheKey)) {
                return $cache[$cacheKey];
            }
        }
        
        // 从配置文件weixin.php构造
        return $this->getWxInvokerByConfigFile($id);
    }
    
    /**
     * 更新access_token
     * @param string $id access_token对应的唯一标识符,每个公众号\公众号\订阅号一个
     * @param string $accessToken access_token
     * @param datetime $expireTime 过期时间
     */
    public function updateAccessToken($id, $accessToken, $expireTime, $authRefreshToken = '')
    {
        // 更新到数据缓存
        $wxInvoker = $this->getWxInvoker($id);
        $wxInvoker->accessToken = $accessToken;
        $wxInvoker->expireTime = $expireTime;
        $cache = \Yii::$app->cache;
        $cacheKey = self::getWxInvokerCacheKey($id);
        $cache[$cacheKey] = $wxInvoker;
    }
    
    /**
     * 从仓储中读取指定键的值
     * @param string $id access_token对应的唯一标识符,每个公众号\公众号\订阅号一个
     * @param string $configKey 配置键
     * @throws WeixinException
     */
    public function getConfigValue($id, $configKey)
    {
        $wxConfig = \Yii::$app->params[$id];
        if (!isset($wxConfig[$configKey])) {
            throw new WeixinException("weixin配置文件中未找到$configKey");
        }
        
        return $wxConfig[$configKey];
    }
    
    public function updateConfigValue($id, $configKey, $configValue)
    {
        throw new \app\framework\webService\Exceptions\NotImplementedException();
    }
    
    /**
     * 通过读取配置文件（weixin.php），初始化一个wxInvoker对象
     * @param type $id
     * @return \app\framework\weixin\WxInvoker
     * @throws WeixinException
     */
    private function getWxInvokerByConfigFile($id)
    {
        $wxInvoker = new WxInvoker();
        $wxInvokeConfig = \Yii::$app->params[$id];
        if (empty($wxInvokeConfig['appId']) || empty($wxInvokeConfig['appSecret']) || empty($wxInvokeConfig['appType'])) {
            throw new WeixinException("未配置appId或appSecret");
        }
        
        $wxInvoker->appId = $wxInvokeConfig['appId'];
        $wxInvoker->appSecret = $wxInvokeConfig['appSecret'];
        $wxInvoker->appType = $wxInvokeConfig['appType'];
        return $wxInvoker;
    }
    
    private static function getWxInvokerCacheKey($id)
    {
        return "wechat_invoker_{$id}";
    }
    
    public static function clearCache($id)
    {
        $cache = \Yii::$app->cache;
        $cacheKey = self::getWxInvokerCacheKey($id);
        $cache->exists($cacheKey) && $cache->delete($cacheKey);
    }
}
