<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 跳转URL
 * 用户点击view类型按钮后，微信客户端将会打开开发者在按钮中填写的网页URL，
 * 可与网页授权获取用户基本信息接口结合，获得用户基本信息。
 * @author Chenxy
 */
class ViewButton extends BaseButton
{
    public $type = 'view';
    
    /**
     * 网页链接，用户点击菜单可打开链接，不超过256字节
     * @var string
     */
    public $url;
}
