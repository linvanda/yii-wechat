<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

/**
 * Description of BaseComponentHandler
 * 第三方开发平台消息推送
 * @author chenxy
 */
class BaseComponentHandler extends BaseHandler
{
    /**
     * 声明该handler能够处理事件类型
     * @return array
     */
    public function getHandlers()
    {
        return ['component_verify_ticket',
                'unauthorized',
                'authorized',
                'updateauthorized'
        ];
    }
    
    /**
     * 推送component_verify_ticket
     * @param array $data
     * @return string
     */
    public function component_verify_ticket($data)
    {
        return '';
    }
    
    /**
     * 推送取消授权通知
     * @param array $data
     * @return string
     */
    public function unauthorized($data)
    {
        return '';
    }
    
    /**
     * 推送授权成功通知
     * @param array $data
     * @return string
     */
    public function authorized($data)
    {
        return '';
    }
    
    /**
     * 推送更新授权通知
     * @param array $data
     * @return string
     */
    public function updateauthorized($data)
    {
        return '';
    }
}
