<?php

namespace app\framework\weixin\helper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 微信自定义菜单按钮基类
 *
 * @author Chenxy
 */
class BaseButton
{
    /**
     * 名称
     * @var string
     */
    public $name;
    
    /**
     * 子按钮
     * @var array
     */
    public $sub_button = [];
}
