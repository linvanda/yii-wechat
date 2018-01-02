<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 微信商户支付证书存储接口
 * @author chenxy
 */
interface IWxMchPayCertStore
{
    /**
     * 获取API支付证书
     */
    public function getSSLCert();
    
    /**
     * 获取API支付密钥
     */
    public function getSSLKey();
}
