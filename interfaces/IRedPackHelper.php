<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * IRedPackHelper（写日志，生成商户订单号）
 * @author chenxy
 */
interface IRedPackHelper
{
    public function getWxMchInfo();
    public function log($requestXml, $reponseXml, $exception = null);
    public function makeBillNo($mchId);
}
