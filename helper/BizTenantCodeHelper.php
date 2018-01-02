<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * Description of BizTenantCodeHelper
 *
 * @author chenxy <chenxy@mysoft.com.cn>
 */
class BizTenantCodeHelper
{
    const TENANT_APPID_MAPPING_CACHE_KEY_PREFIX = "tenant_appid_mapping_info_";
    
    /**
     * 根据公众号appId查找租户代码
     * @param string $appId
     * @return boolean|string 找不到返回false
     */
    public static function getTenantCodeByAppId($appId)
    {
        if (empty($appId)) {
            throw new \InvalidArgumentException("根据appid获取租户代码失败，appId不能为空");
        }
        
        $cache = \Yii::$app->cache;
        $cacheKey = static::TENANT_APPID_MAPPING_CACHE_KEY_PREFIX . $appId;
        if ($cache->exists($cacheKey)) {
            return $cache[$cacheKey];
        }
        
        // 根据appId查找租户代码
        $query = new \yii\db\Query();
        $tenantCode = $query->from('wechat_account_mapping')
            ->where('account_app_id =:app_id and is_deleted=0', [':app_id' => $appId])
            ->select('tenant_code')
            ->createCommand()
            ->queryScalar();

        if (empty($tenantCode)) {
            return "";
        }
        
        $cache[$cacheKey] = $tenantCode;
        return $tenantCode;
    }
    
    /**
     * 清除缓存
     * @param type $appId
     */
    public static function clearTenantAppIdMappingCache($appId)
    {
        $cache = \Yii::$app->cache;
        $cacheKey = static::TENANT_APPID_MAPPING_CACHE_KEY_PREFIX . $appId;
        $cache->exists($cacheKey) && $cache->delete($cacheKey);
    }
}
