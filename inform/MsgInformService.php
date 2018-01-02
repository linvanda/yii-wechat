<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\inform;

/**
 * 微信客服消息通知
 *
 * @author Chenxy
 */
class MsgInformService
{
    /**
     * 入列
     * @param string $openid
     * @param string $accountId
     * @param string $msgType 允许的值：'文字','图片','图文','语音','音乐','视频'
     * @param string $msgContent
     * @return guid msgID
     */
    public static function insert($openid, $accountId, $msgType, $msgContent)
    {
        $informService = \Yii::$container->get('app\framework\weixin\inform\IInformService');
        return $informService->insert($openid, $accountId, $msgType, $msgContent);
    }
    
    /**
     * 出列
     * @param guid $msgId
     * @return int
     */
    public static function remove($msgId)
    {
        $informService = \Yii::$container->get('app\framework\weixin\inform\IInformService');
        return $informService->remove($msgId);
    }
    
    /**
     * 搜索队列消息
     * @param string $accountId
     * @param string $openid
     * @return array
     */
    public static function search($accountId, $openid)
    {
        $informService = \Yii::$container->get('app\framework\weixin\inform\IInformService');
        return $informService->search($accountId, $openid);
    }
}
