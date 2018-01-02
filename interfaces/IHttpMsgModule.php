<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use app\framework\weixin\msg\MessageServer;

/**
 *
 * @author Administrator
 */
interface IHttpMsgModule
{
    public function init(MessageServer $app);
}
