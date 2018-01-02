<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin;

require_once(dirname(__FILE__).'/../3rd/phpqrcode/MyQrCode.php');
/**
 * Description of QrCodeLoginHelper
 *
 * @author Chenxy
 */
class QrCodeLoginHelper 
{
    /**
     * 生成扫描登陆用二维码
     * @param string $corpId 企业号id
     * @param string $controller 处理该登陆的controller
     * @param string $action 处理该登陆的action
     * @param array|string $params action后的参数
     * @return object {'url' : 生成的二维码图片http访问地址, 'key':身份id}
     */
    public static function createLoginQrCode($corpId, $controller = 'auth', $action = 'qrauth', $params = [])
    {
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'HTTPS') === false ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $redirectUrl = "{$protocol}://{$host}/index.php?r=$controller/$action";
        $hashKey = static::getKey();
        $params['key'] = $hashKey;
        foreach ($params as $key => $value) {
            $redirectUrlParams[] = "$key=$value";
        }
        $redirectUrl .= strpos($redirectUrl, '?') === false ? '?' : '&';
        $redirectUrl .= implode('&', $redirectUrlParams);
        
        $urlParams['appid'] = $corpId;
        $urlParams['redirect_uri'] = $redirectUrl;
        $urlParams['response_type'] = 'code';
        $urlParams['scope'] = 'snsapi_base';
        $info = 'https://open.weixin.qq.com/connect/oauth2/authorize?' . http_build_query($urlParams) . '#wechat_redirect';
        $qrImgFile = static::createOssQRCode($info);
        return (object)['url' => $qrImgFile, 'key' => $hashKey];
    }

    /**
     * 生成跳转路径的二维码
     * @param string $url
     * @return mixed
     */
    public static function createQRCode($url)
    {
        return static::createOssQRCode($url);
    }

    /**
     * 生成OSS跳转路径的二维码
     * @param string $url
     * @return int
     */
    public static function createOssQRCode($url, $reuse = true)
    {
        $hashKey = static::getKey();
        // 二维码数据
        $qrImgFile = \MyQrCode::create_oss_qr($url, '', 0, $reuse);
        return (object)['url' => $qrImgFile, 'key' => $hashKey];
    }
    
    /**
     * 获取身份key,用于action中验证合法性
     * @return string
     */
    private static function getKey()
    {
        $time = time();
        $rand = rand(10000,99999);
        $key = sha1(strval($time + $rand), false);
        return $key;
    }
}
