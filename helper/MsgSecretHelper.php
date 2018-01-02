<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * Description of MsgSecretHelper
 *
 * @author chenxy <chenxy@mysoft.com.cn>
 */
class MsgSecretHelper
{
    /**
     * 验证签名 
     * @param type $encryptXml
     * @param type $token
     * @return type
     */
    public static function validataSignature($encryptXml, $token)
    {
        $data = self::extract($encryptXml);
        //$theTenantDb = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn('mysoft');
        //$theTenantDb->createCommand()->insert('t_data_sync_log', ['id' => \app\framework\utils\StringHelper::uuid(), 'data_type' => 'pc_wechat_member_queue_encryptdata', 'data_id' => '123', 'data' => print_r($data, true)])->execute();

        if (!key_exists("MsgSignature", $data)) {
            return false;
        }
        $expectSignature = self::createSignature($encryptXml, $token);
        //$theTenantDb->createCommand()->insert('t_data_sync_log', ['id' => \app\framework\utils\StringHelper::uuid(), 'data_type' => 'pc_wechat_member_queue_expectSignature', 'data_id' => '123', 'data' => print_r($expectSignature, true)])->execute();
        return $expectSignature === $data["MsgSignature"];
    }
    
    /**
     * 生成签名
     * @param type $encryptXml
     * @param type $token
     * @return type
     * @throws \app\framework\weixin\WeixinException
     */
    public static function createSignature($encryptXml, $token)
    {
        $data = self::extract($encryptXml);
        
        // 验证格式
        if (!key_exists("Encrypt", $data) || !key_exists("TimeStamp", $data) || !key_exists("Nonce", $data)) {
            throw new \app\framework\weixin\WeixinException("消息包内容格式不正确:" . $encryptXml);
        }
        
        // 生成签名
        $encryptMsg = $data["Encrypt"];
        $timestamp = $data["TimeStamp"];
        $nonce = $data["Nonce"];
        $array = array($encryptMsg, $token, $timestamp, $nonce);
        sort($array, SORT_STRING);
        $str = implode($array);
        return sha1($str);
    }
    
    private static function extract($encryptXml)
    {
        $xmldata = new \SimpleXMLElement($encryptXml);

        // 转换成数组
        $data = [];
        foreach ($xmldata as $key => $value) {
            $data[$key] = strval($value);
        }
        
        if (!key_exists("TimeStamp", $data) && $_GET["timestamp"]) {
            $data["TimeStamp"] = $_GET["timestamp"];
        }

        if (!key_exists("Nonce", $data) && $_GET["nonce"]) {
            $data["Nonce"] = $_GET["nonce"];
        }
        
        if (!key_exists("MsgSignature", $data) && $_GET["msg_signature"]) {
            $data["MsgSignature"] = $_GET["msg_signature"];
        }
            
        return $data;
    }
}
