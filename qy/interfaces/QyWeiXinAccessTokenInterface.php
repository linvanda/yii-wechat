<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 13:28
 */

namespace app\framework\weixin\qy\interfaces;

/**
 * 企业微信AccessToken接口
 * Interface QyWeiXinAccessTokenInterface
 * @package app\framework\weixin\qy\interfaces
 */
interface QyWeiXinAccessTokenInterface extends QyWeiXinConfigInterface
{
    /**
     * 获取企业微信AccessToken
     * @param $appCode
     * @param $opToken
     * @return mixed
     */
    public function getAccessToken($appCode, $opToken = '');

    /**
     * 企业微信AccessToken缓存key
     * @param $appCode
     * @return mixed
     */
    public function getAccessTokenCacheKey($appCode);

    /**
     * 清除企业微信AccessToken缓存
     * @param $appCode
     * @return mixed
     */
    public function clearAccessTokenCache($appCode);
}
