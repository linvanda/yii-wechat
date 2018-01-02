<?php

namespace app\framework\weixin\proxy;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiBase
 *
 * @author Chenxy
 */
use app\framework\weixin\interfaces\IAccessTokenHelper;
use app\framework\weixin\WeixinException;
use app\framework\utils\DateTimeHelper;

class ApiBase
{
    /**
     * AccessTokenHelper
     * @var IAccessTokenHelper
     */
    protected $_accessTokenHelper;
    
    /**
     * 设置执行次数，最小值为1，重试次数=$_maxInvokeTimes -1
     * @var int
     */
    protected $_maxInvokeTimes = 1;
    
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        $this->_accessTokenHelper = $accessTokenHelper;
    }
    
    /**
     * 设置调用接口次数
     * @param int $invokeTimes
     * @throws \InvalidArgumentException
     */
    public function setMaxInvokeTimes(int $invokeTimes)
    {
        if ($invokeTimes < 1) {
            throw new \InvalidArgumentException('必须为大于0的整数');
        }
        
        $this->_maxInvokeTimes = $invokeTimes;
    }

    /**
     * 调用接口
     * @param string $uri 接口URL
     * @param string $method 'GET\POST\PUT' HTTP方法
     * @param string $apiDescription 接口描述
     * @param array $data 接口数据 get方法时可作为url参数数据， post方法时作为传输的数据
     * @param bool $autoAppendAccessTokenParam 是否url自动带入accesstoken参数
     * @param bool $jsonParse 是否对返回结果进行json反序列化，默认true
     * @return object 微信接口返回结果
     * @throws \InvalidArgumentException
     * @throws WeixinException
     */
    protected function execute($uri, $method, $apiDescription, $data = [], $autoAppendAccessTokenParam = true, $jsonParse = true)
    {
        if (!in_array($method, ['GET','POST','PUT'])) {
            throw new \InvalidArgumentException('参数值只能为GET、POST、PUT');
        }
        
        if (strpos(strtolower(json_encode($data)), "access_token") !== false) {
            throw new WeixinException("调用[$apiDescription]失败，数据包中含有access_token字符串，请求已被拦截 数据包内容:" . json_encode($data, JSON_UNESCAPED_UNICODE));
        }
        
        $invokeUri = $autoAppendAccessTokenParam ? $this->getWithAccessToken($uri) : $uri;
        $restClient = new \app\framework\webService\RestClientHelper();
        $occurError = false;
        $accessTokenExpiredTryTimes = 0;
        // 调用微信接口
        for ($i = 0; $i < $this->_maxInvokeTimes; $i++) {
            $result = $restClient->invoke($invokeUri, $data, $method, false);
            // 写日志
            $this->writeInvokeLog($method, $invokeUri, DateTimeHelper::now(), json_encode($data, JSON_UNESCAPED_UNICODE));
            // 返回成功
            if (strpos($result, '"errcode":') === false || strpos($result, '"errcode":0') !== false) {
                    $occurError = false;
                    break;
            }
            // 失败重试
            $occurError = true;
            $errorResult = json_decode($result);
            $this->_accessTokenHelper->makeExpire($errorResult->errcode);
            // access_token过期自动重试3次
            if ($autoAppendAccessTokenParam && $this->_accessTokenHelper->checkIsExpired($errorResult->errcode) && $accessTokenExpiredTryTimes < 3) {
                $i--;
                $accessTokenExpiredTryTimes++;
                $this->logAccessTokenExpired($invokeUri, $result, $accessTokenExpiredTryTimes);
                $invokeUri = $this->getWithAccessToken($uri);
            }
        }
        
        if ($occurError) {
            $msg = "调用[$apiDescription]失败，错误码:[$errorResult->errcode] 消息:[$errorResult->errmsg] 参数:"
                    . (count($data) == 0 ? '无':  json_encode($data, JSON_UNESCAPED_UNICODE))
                    . "] 接口url:[{$invokeUri}]";
            
            if ($this->checkIsWarning($errorResult->errcode)) {
                \Yii::warning($msg);
            } else {
                throw new WeixinException($msg, $errorResult->errcode);
            }
        }
        
        return $jsonParse ? json_decode($result) : $result;
    }
    
    private function checkIsWarning($errcode)
    {
        return in_array($errcode, [
            43004, // 未关注或者客服消息接口48小时内未发生交互
            40003, // 不合法的OpenID
            43004, // 需要接收者关注
        ]);
    }
    
    private function logAccessTokenExpired($url, $errInfo, $retryTimes)
    {
        // 生产环境和beta不记录
        if (YII_ENV == 'prod' || YII_ENV == 'beta') {
            return;
        }

        $newAccessToken = $this->_accessTokenHelper->getAccessToken();
        $now = DateTimeHelper::now();
        $msg =  "{$now}:微信{$url} access_token过期重试[$retryTimes],{$errInfo},刷新后的access_token:{$newAccessToken}";
        \Yii::warning($msg);
    }
    
    protected function getWithAccessToken($uri)
    {
        $accessTokenParamName = $this->_accessTokenHelper->getAccessTokenParamName();
        $accessToken = $this->_accessTokenHelper->getAccessToken();
        $uri .= strpos($uri, '?') === false ? '?' : '&';
        $uri .= "{$accessTokenParamName}={$accessToken}";
        return $uri;
    }
    
    private function writeInvokeLog($method, $invokeUrl, $invokeTime, $parameter)
    {
        try {
            // 只记录群发日志包括错误和access_token过期重试
            $logFilter = stripos($invokeUrl, "https://api.weixin.qq.com/cgi-bin/message/mass/send" !== false)
            || stripos($invokeUrl, "https://api.weixin.qq.com/cgi-bin/message/mass/sendall" !== false);
            
            if ($logFilter && method_exists($this->_accessTokenHelper, "writeLog")) {
                $this->_accessTokenHelper->writeLog($method, $invokeUrl, $invokeTime, $parameter);
            } 
        } catch (\Exception $ex){
            \Yii::warning($ex);
        }
    }
}
