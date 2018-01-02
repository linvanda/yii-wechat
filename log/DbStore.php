<?php

namespace app\framework\weixin\log;

use app\framework\db\SqlHelper;

class DbStore implements StoreInterface
{

    /**
     * @param string $fromUserName 发送方帐号（一个OpenID）
     * @param string $toUserName 开发者微信号
     * @param string $calledUrl called by weixin
     * @param datetime $receiveTime 接收到事件时间
     * @param int $msg_timestamp 消息创建时间
     * @param string $msgType 消息类型，event
     * @param string $original_xml 微信传入的完整xml内容
     */
    public function insert($fromUserName, $toUserName, $calledUrl, $receiveTime, $msg_timestamp, $msgType, $original_xml)
    {
        $row = [
            'from_user_name' => $fromUserName,
            'to_user_name' => $toUserName,
            'called_url' => $calledUrl,
            'receive_time' => $receiveTime,
            'msg_timestamp' => $msg_timestamp,
            'msg_type' => $msgType,
            'original_xml' => $original_xml
        ];

        $tenantCode = $this->getTenantCode($msgType);
        $conn = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn($tenantCode);
        SqlHelper::insert('p_msg', $conn, $row, false);
    }
    
    private function getTenantCode($msgType)
    {
        // 全网发布和组件消息时直接记录到体验库
        if ($msgType == "publish" || $msgType == "ticket") {
            if (YII_ENV == 'prod' || YII_ENV == 'beta' || YII_ENV == 'dev') {
                return "mysoft";
            } else if (YII_ENV == "test" || YII_ENV == 'auto_test') {
                return "functest";
            }
        }
        
        // 托管模式,根据appId查找租户代码
        $appId = $_GET["appid"];
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
        $tenantReader= \Yii::$container->get('app\framework\biz\tenant\TenantReaderInterface');
        // 读取企业代码
        return $tenantReader->getCurrentTenantCode();
    }
}