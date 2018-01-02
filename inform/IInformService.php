<?php

namespace app\framework\weixin\inform;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 微信自定义客服消息通知接口
 * @author Chenxy
 */
interface IInformService
{
    /**
     * 入列
     * @param string $toUser
     * @param string $formUser
     * @param string $type
     * @param string $content
     */
    public function insert($toUser, $formUser, $type, $content);
    
    /**
     * 出列
     * @param type $msgId
     */
    public function remove($msgId);
    
    /**
     * 搜索列
     */
    public function search($fromUser, $toUser);
}
