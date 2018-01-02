<?php

namespace app\framework\weixin\proxy\fw;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 微信认证设备相关接口
 *
 * @author Zengsy
 */
use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

class Device extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }

    public function getQrcode()
    {
        return $this->execute("https://api.weixin.qq.com/device/getqrcode", "GET", "获取deviceid和设备二维码");
    }

    public function authorize($id, $mac, $op_type = "1", $connect_protocol = "3", $auth_key = "", $close_strategy = "1", $conn_strategy = "1", $crypt_method = "0", $auth_ver = "0", $manu_mac_pos = "-1", $ser_mac_pos = "-2")
    {
        $data = [
            "device_num" => "1",
            "op_type" => $op_type,
            "device_list" => [
                [
                    "id" => $id,
                    "mac" => $mac,
                    "connect_protocol" => $connect_protocol,
                    "auth_key" => $auth_key,
                    "close_strategy" => $close_strategy,
                    "conn_strategy" => $conn_strategy,
                    "crypt_method" => $crypt_method,
                    "auth_ver" => $auth_ver,
                    "manu_mac_pos" => $manu_mac_pos,
                    "ser_mac_pos" => $ser_mac_pos
                ]
            ]
        ];

        return $this->execute("https://api.weixin.qq.com/device/authorize_device", "POST", "微信设备授权", $data);
    }
    
    
    
    public function bind($device_id, $openid, $ticket)
    {
        $data = [
            "ticket" => $ticket,
            "device_id" => $device_id,
            "openid" => $openid
        ];

        return $this->execute("https://api.weixin.qq.com/device/bind", "POST", "绑定设备", $data);
    }
    
    public function unbind($device_id, $openid, $ticket)
    {
        $data = [
            "ticket" => $ticket,
            "device_id" => $device_id,
            "openid" => $openid
        ];

        return $this->execute("https://api.weixin.qq.com/device/unbind", "POST", "解绑设备", $data);
    }
    
    public function compelBind($device_id, $openid)
    {
        $data = [
            "device_id" => $device_id,
            "openid" => $openid
        ];

        return $this->execute("https://api.weixin.qq.com/device/compel_bind", "POST", "强制绑定设备", $data);
    }
    
    public function compelUnbind($device_id, $openid)
    {
        $data = [
            "device_id" => $device_id,
            "openid" => $openid
        ];

        return $this->execute("https://api.weixin.qq.com/device/compel_unbind", "POST", "强制解绑设备", $data);
    }
    
    public function getBindDevice($openId)
    {
        $data = [
            "openid" => $openId
        ];

        return $this->execute("https://api.weixin.qq.com/device/get_bind_device", "GET", "获取用户绑定设备", $data);
    }
    
    public function getBindUser($deviceId, $device_type)
    {
        $data = [
            "device_id" => $deviceId,
            "device_type" => $device_type
        ];

        return $this->execute("https://api.weixin.qq.com/device/get_openid", "GET", "获取设备绑定用户", $data);
    }
}
