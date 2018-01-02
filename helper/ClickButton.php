<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 点击推事件
 * 用户点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event	
 * 的结构给开发者（参考消息接口指南），并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互
 * @author Chenxy
 */
class ClickButton extends BaseButton
{
    public $type = 'click';
    
    /**
     * 菜单KEY值，用于消息接口推送，不超过128字节
     * @var string
     */
    public $key;
}
