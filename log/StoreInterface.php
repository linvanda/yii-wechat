<?php

namespace app\framework\weixin\log;

interface StoreInterface
{
    /**
     * @param string $fromUserName 发送方帐号（一个OpenID）
     * @param string $toUserName 开发者微信号
     * @param string $calledUrl called by weixin
     * @param datetime $receiveTime 接收到事件时间
     * @param datetime $msgTime 消息创建时间
     * @param string $msgType 消息类型，event
     * @param string $original_xml 微信传入的完整xml内容
     */
    public function insert($fromUserName, $toUserName, $calledUrl, $receiveTime, $msgTime, $msgType, $original_xml);

}