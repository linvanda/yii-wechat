<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * IAccessTokenHelper
 * @author Chenxy
 */
interface IAccessTokenHelper
{
    /**
     * 获取access_token
     */
    public function getAccessToken();
    
    /**
     * 指定access_token参数名
     */
    public function getAccessTokenParamName();

    /**
     * 设置access_token过期，无参时强制过期
     * @param type $errorCode
     */
    public function makeExpire($errorCode = -1);
    
    /**
     * 根据错误码检测是否accessToken已过期
     * @param type $errorCode
     */
    public function checkIsExpired($errorCode);
    
    /**
     * 获取唯一标识
     */
    public function getId();
    
    /**
     * 获取微信appid
     */
    public function getAppId();
}
