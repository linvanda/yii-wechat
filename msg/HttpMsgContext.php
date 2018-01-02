<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

use app\framework\weixin\msg\HttpMsgRequest;
use app\framework\weixin\msg\HttpMsgResponse;

/**
 * Description of HttpMsgContext
 *
 * @author chenxy
 */
class HttpMsgContext
{
    /**
     * request
     * @var \app\framework\weixin\msg\HttpMsgRequest
     */
    public $request;
    
    /**
     * response
     * @var \app\framework\weixin\msg\HttpMsgResponse
     */
    public $response;
    
    public function __construct(HttpMsgRequest $request = null, HttpMsgResponse $reponse = null)
    {
        $this->request = $request;
        $this->response = $reponse;
    }
}
