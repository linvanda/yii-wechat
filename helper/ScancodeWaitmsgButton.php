<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 扫码推事件且弹出“消息接收中”提示框
 * 用户点击按钮后，微信客户端将调起扫一扫工具，完成扫码操作后，将扫码的结果传给开发者，
 * 同时收起扫一扫工具，然后弹出“消息接收中”提示框，随后可能会收到开发者下发的消息
 * @author Chenxy
 */
class ScancodeWaitmsgButton extends BaseButton
{
    public $type = 'scancode_waitmsg';
    
    /**
     * 菜单KEY值，用于消息接口推送，不超过128字节
     * @var string
     */
    public $key;
}
