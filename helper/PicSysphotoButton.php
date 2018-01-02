<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 弹出系统拍照发图
 * 用户点击按钮后，微信客户端将调起系统相机，完成拍照操作后，会将拍摄的相片发送给开发者，
 * 并推送事件给开发者，同时收起系统相机，随后可能会收到开发者下发的消息。
 * @author Chenxy
 */
class PicSysphotoButton extends BaseButton
{
    public $type = 'pic_sysphoto';
    
    /**
     * 菜单KEY值，用于消息接口推送，不超过128字节
     * @var string
     */
    public $key;
}
