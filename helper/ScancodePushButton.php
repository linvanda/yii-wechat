<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 扫码推事件
 * 用户点击按钮后，微信客户端将调起扫一扫工具，完成扫码操作后显示扫描结果（如果是URL，将进入URL），
 * 且会将扫码的结果传给开发者，开发者可以下发消息
 * @author Chenxy
 */
class ScancodePushButton extends BaseButton
{
    public $type = 'scancode_push';
    
    /**
     * 菜单KEY值，用于消息接口推送，不超过128字节
     * @var string
     */
    public $key;
}
