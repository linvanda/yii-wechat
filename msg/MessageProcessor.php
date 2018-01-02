<?php

namespace app\framework\weixin\msg;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 消息处理器
 *
 * @author Chenxy
 */
class MessageProcessor
{
    private $_handlers = [];
    
    /**
     * 构造方法
     * @param array $handlers
     */
    public function __construct($handlers = [])
    {
        $this->_handlers = $handlers;
    }
    
    /**
     * 安装handler
     * @param BaseHandler的实现类 $handler
     */
    public function install($handler)
    {
        $this->_handlers[] = $handler;
        return $this;
    }

    /**
     * 获取相应的handler
     * @param type $eventHandle
     * @return type
     */
    private function getHandler($eventHandle)
    {
        foreach ($this->_handlers as $handler) {
            $handlers = $handler->getHandlers();
            if (in_array($eventHandle, $handlers)) {
                return $handler;
            }
        }
        
        return null;
    }

    /**
     * 消息处理
     * @param array $data 要处理数据包
     * @param string $action 处理函数
     */
    public function run($data, $action)
    {
        // 获取事件处理方法
        $handler = $this->getHandler($action);
        if (is_null($handler)) {
            // 忽略事件放在这里
            if (in_array($action, ['wificonnected'])) {
                return '';
            }
            
            throw new \Exception("未找到处理handler:{$action}");
        }
        // 调用处理器的处理方法
        $data = $handler->beforeHandle($data);
        return $handler->$action($data);
    }
}
