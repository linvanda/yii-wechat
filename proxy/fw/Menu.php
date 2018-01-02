<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

/**
 * 自定义菜单相关接口
 *
 * @author Chenxy
 */
use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

class Menu extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 创建自定义菜单
     * @param array $buttons,可通过app\framework\weixin\helper\ButtonFactory创建
     * @return object {"errcode":0,"errmsg":"ok"}
     */
    public function create($buttons)
    {
        $params =['button' => $buttons];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/menu/create', 'POST', '创建自定义菜单', $params);
        return $result;
    }
    
    /**
     * 查询自定义菜单
     * @return type
     */
    public function get()
    {
        $params =[];
        $buttons = $this->execute('https://api.weixin.qq.com/cgi-bin/menu/get', 'GET', '查询自定义菜单', $params);
        return $buttons;
    }
    
    /**
     * 删除自定义菜单
     * @return type
     */
    public function delete()
    {
        $params =[];
        $buttons = $this->execute('https://api.weixin.qq.com/cgi-bin/menu/delete', 'GET', '删除自定义菜单', $params);
        return $buttons;
    }
    
    /**
     * 获取自定义菜单配置
     * @return type
     */
    public function getCurrentSelfmenuInfo()
    {
        $params =[];
        $buttons = $this->execute('https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info', 'GET', '获取自定义菜单配置', $params);
        return $buttons;
    }
}
