<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * IAccessTokenRepository
 * @author Chenxy
 */
interface IAccessTokenRepository
{
    /**
     * 读取微信调用者
     * @param string $id 唯一标识键值
     * @param bool $useCache 是否用缓存
     */
    public function getWxInvoker($id, $useCache = true);
    
    /**
     * 更新access_token
     * @param string $id access_token对应的唯一标识符
     * @param string $accessToken access_token
     * @param datetime $expireTime 过期时间
     */
    public function updateAccessToken($id, $accessToken, $expireTime);
    
    /**
     * 从仓储中读取指定键的值
     * @param string $id 唯一标识键值
     * @param string $configKey 键名
     */
    public function getConfigValue($id, $configKey);
    
    /**
     * 更新仓储中指定键的值
     * @param string $id 唯一标识键值
     * @param string $configKey 键名
     * @param string $configValue 键值
     */
    public function updateConfigValue($id, $configKey, $configValue);
}
