<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 12:46
 */

namespace app\framework\weixin\qy\interfaces;

interface QyWeiXinConfigInterface
{
    /**
     * 获取企业微信配置信息
     * 数据返回格式:['corp_id'=>'wx866714adbc41dd6a','corp_secret'=>'G3kY3J5vgt9P6GCtYLLhonL62gCZG042G_8JC2NIvlh-BpJhfejxaZ9Qt-fKX7_1','agent_id'=>'1']
     * @param string $appCode 应用Code
     * @return mixed
     */
    public function getConfig($appCode);

    /**
     * 获取配置信息缓存key
     * @param $appCode
     * @return mixed
     */
    public function getConfigCacheKey($appCode);

    /**
     * 清空奇艺网微信配置信息缓存
     * @param $appCode
     * @return mixed
     */
    public function clearConfigCache($appCode);
}
