<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\framework\weixin;

/**
 * Description of WeixinException
 *
 * @author Chenxy
 */
class WeixinException extends \Exception
{
    /**
     * 构造方法
     * @param string $message 微信返回的错误消息
     * @param int $code 微信返回的错误码
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }
    
    /**
     * 转换为字符串
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
