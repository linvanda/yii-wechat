<?php

namespace app\framework\weixin\proxy\qy;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 微信用户类接口
 *
 * @author Chenxy
 */
use app\framework\weixin\interfaces\IAccessTokenHelper;
use app\framework\weixin\proxy\ApiBase;

class User extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 根据code获取用户信息，权限要求说明：管理员须拥有agent的使用权限；agentid必须和跳转链接时所在的企业应用ID相同。
     * @param type $code
     * @param type $agentid
     * @return object {
                            "UserId":"USERID",
                            "DeviceId":"DEVICEID"
                       }
     */
    public function getUserInfo($code, $agentid)
    {
        $params =['code' => $code, 'agentid' => $agentid];
        $userInfo = $this->execute('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo', 'GET', '通过code获取用户信息', $params);
        return $userInfo;
    }
    
    /**
     * 根据用户id获取成员信息，该接口的权限要求：管理员须拥有’获取成员’的接口权限，以及成员的查看权限。
     * @param string $userId,通过getUserInfo接口获取
     * @return {
                    "errcode": 0,
                    "errmsg": "ok",
                    "userid": "zhangsan",
                    "name": "李四",
                    "department": [1, 2],
                    "position": "后台工程师",
                    "mobile": "15913215421",
                    "email": "zhangsan@gzdev.com",
                    "weixinid": "lisifordev",
                    "avatar": "http://wx.qlogo.cn/mmopen/ajNVdqHZLLA3WJ6DSZUfiakYe37PKnQhBIeOQBO4czqrnZDS79FH5Wm5m4X69TBicnHFlhiafvDwklOpZeXYQQ2icg/0",
                    "status": 1,
                    "extattr": {"attrs":[{"name":"爱好","value":"旅游"},{"name":"卡号","value":"1234567234"}]}
                 }
     */
    public function get($userId)
    {
        $params =['userid'=>$userId];
        $userInfo = $this->execute('https://qyapi.weixin.qq.com/cgi-bin/user/get', 'GET', '通过id获取用户信息', $params);
        return $userInfo;
    }
}
