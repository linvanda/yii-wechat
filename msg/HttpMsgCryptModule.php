<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;
require_once(__DIR__  . '/../../3rd/wxmsg/wxBizMsgCrypt.php');

use app\framework\weixin\msg\HttpMsgContext;

/**
 * Description of HttpMsgCryptModule
 *
 * @author chenxy
 */
class HttpMsgCryptModule implements \app\framework\weixin\interfaces\IHttpMsgModule
{
    public function init(MessageServer $app)
    {
        $app->beginRequestEvents[__CLASS__] = 'begin_request_event';
        $app->endRequestEvents[__CLASS__] = 'end_request_event';
    }
    
    private static $_cryptArray = [];


    // 解密请求数据包
    public static function begin_request_event(HttpMsgContext $context, $args)
    {
        // 解密数据包
        $msgSign = $context->request->getQueryParam('msg_signature');
        $timestamp = $context->request->getQueryParam('timestamp');
        $nonce = $context->request->getQueryParam('nonce');
        $msgXml = '';
        $msgCrypt = static::getCrypt($context, $args);
        $errCode = $msgCrypt->decryptMsg($msgSign, $timestamp, $nonce, $context->request->requestXml, $msgXml);
        if ($errCode != \ErrorCode::$OK) {
            throw new \app\framework\weixin\WeixinException("解密消息包出错，错误码：{$errCode}, 消息包：" . $context->request->requestXml);
        }
        
        $context->request->requestXml = $msgXml;
    }
    
    // 加密响应数据包
    public static function end_request_event(HttpMsgContext $context, $args)
    {
        // 直接回复的不需要加密
        if (in_array($context->response->responseXml, ['', 'success'])) {
            return;
        }

        $timeStamp = time();
        $msgCrypt = static::getCrypt($context, $args);
        $nonce = static::createNonce();
        $msgXml = '';
        $errCode = $msgCrypt->encryptMsg($context->response->responseXml, $timeStamp, $nonce, $msgXml);
        if ($errCode != \ErrorCode::$OK) {
            throw new \app\framework\weixin\WeixinException("加密消息包出错，错误码：{$errCode}, 消息包：" . $context->response->responseXml);
        }
        
        $context->response->responseXml = $msgXml;
    }
    
    private static function getCrypt(HttpMsgContext $context, $args)
    {
        // 先从内存中取
        $isThirdMsg = key_exists("third", $_GET);
        $appId = $isThirdMsg ? $_GET['appid'] : \app\framework\weixin\proxy\component\WxComponent::getComponentAppId();
        if (key_exists($appId, static::$_cryptArray)) {
            return static::$_cryptArray[$appId];
        }

        // 支持来自微信和第三方的消息加解密
        $repo = $isThirdMsg
                ? (new \app\framework\weixin\DbAccessTokenRepository())
                : (new \app\framework\weixin\component\ComponentAccessTokenRepository());
        $token = $repo->getConfigValue($appId, 'token');
        $encodingAesKey = $repo->getConfigValue($appId, 'encoding_key');
        if (empty($token) || empty($encodingAesKey) || empty($appId)) {
            throw new \app\framework\weixin\WeixinException("appId:{$appId}未配置加密参数token、encoding_key");
        }
        $msgCrypt = new \WXBizMsgCrypt($token, $encodingAesKey, $appId);
        static::$_cryptArray[$appId] = $msgCrypt;
        
        return $msgCrypt;
    }
    
    private static function createNonce()
    {
        $length = 6;
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $chars .= 'abcdefghijklmnopqrstuvwxyz';
        $chars .= '1234567890';

        $unique = '';
        for ($i = 0; $i < $length; $i++) {
            $unique .= substr($chars, (rand() % (strlen($chars))), 1);
        }

        return $unique;
    }
}
