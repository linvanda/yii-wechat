<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 开放平台access_token仓储
 * @author chenxy
 */
interface IComponentAccessTokenRepository extends IAccessTokenRepository
{
    /**
     * 获取验证票据
     * @param string $id
     */
    public function getVerifyTicket($id);

    /**
     * 更新验证票据
     * @param string $id
     */
    public function updateVerifyTicket($id, $ticket);
}
