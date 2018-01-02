<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\mch;

/**
 * Description of WxPayApiBase
 *
 * @author chenxy
 */

abstract class WxPayApiBase
{
    /**
     * 接口请求参数数据数组格式
     * @var array
     */
    protected $requestValues = [];
    
    protected $requestXml = '';

    /**
     * 接口响应数据数据格式
     * @var type
     */
    protected $reponseValues = [];
    
    protected $reponseXml = '';

    /**
     * 获取签名密钥
     * 返回签名密钥字符串
     */
    abstract protected function getSignKey();

    /**
     * 获取微信支付证书
     * 返回值类型：实现\app\framework\weixin\interfaces\IWxMchPayCert接口
     */
    abstract protected function getWxPaySSLCert();
    
    /**
     * 设置请求数据，按接口XML要求的key-pair格式返回数组（除签名）
     */
    abstract protected function setRequestValues();
    
    /**
     * 获取支付指定数据
     * @param string $key
     * @return string|int|bool  $key不存在时返回false
     */
    public function getValue($key)
    {
        return array_key_exists($key, $this->requestValues) ? $this->requestValues[$key] : false;
    }
    
    /**
     * 微信通用要求证书支付接口
     * @param string $apiUrl 支付接口url
     * @throws \app\framework\weixin\exceptions\WxPayApiException
     */
    protected function execute($apiUrl)
    {
        $this->internalExecute($apiUrl);
    }
    
    private function internalExecute($apiUrl)
    {
        // 获取请求数据
        $this->requestValues = array_merge($this->setRequestValues(), $this->requestValues);
        // 生成签名
        $this->setRequestSign();
        $this->requestXml = $this->toXml($this->requestValues);
        // 获取支付证书
        $sslCert = $this->getWxPaySSLCert();
        $certContent = $sslCert->getSSLCert();
        $keyContent = $sslCert->getSSLKey();
        if ($certContent === false || $keyContent === false) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("获取支付证书内容异常");
        }
        // 创建证书存放目录
        $dirCert = defined('TMP_FOLDER') ? rtrim(TMP_FOLDER, '/').'/cert/' : $_SERVER ['DOCUMENT_ROOT'] . "/../protected/cert/";
        if (!file_exists($dirCert)) {
            mkdir($dirCert, 0700);
        }
        $certFile  = $dirCert . $this->requestValues['mch_id'] . "_wxpay_cert.pem";
        file_put_contents($certFile, $certContent, LOCK_EX);
        $keyFile  = $dirCert . $this->requestValues['mch_id'] . "_wxpay_key.pem";
        file_put_contents($keyFile, $keyContent, LOCK_EX);
        // 初始化请求
        $ch = curl_init();
        $this->setCurlOptions($ch, $apiUrl, $certFile, $keyFile);
        // 执行请求
        $reponseXml = curl_exec($ch);
        if ($reponseXml === false) {
            $errCode = curl_errno($ch);
            $errMsg = curl_error($ch);
            curl_close($ch);
            throw new \app\framework\weixin\exceptions\WxPayApiException("curl出错，错误码:{$errCode},错误信息：{$errMsg}");
        }
        curl_close($ch);
        // 有返回值
        $this->setReponseValues($reponseXml);
//        $this->validateReponseSign();
    }
    
    private function setCurlOptions($ch, $apiUrl, $sslCertFile, $sslKeyFile)
    {
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
//        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->requestXml);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $sslCertFile);
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $sslKeyFile);
    }
    
    
    /**
     * 验证返回签名
     * @return boolean
     */
    private function validateReponseSign()
    {
        $pass = true;
        if ($this->reponseValues['return_code'] === 'SUCCESS' && array_key_exists('sign', $this->reponseValues)) {
            $sign = $this->makeSign($this->reponseValues, $this->getSignKey());
            $pass = ($sign === $this->reponseValues['sign']);
        }
        
        if (!$pass) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("签名错误");
        }
    }
    
    /**
     * 设置请求签名
     */
    private function setRequestSign()
    {
        $sign = $this->makeSign($this->requestValues, $this->getSignKey());
        $this->requestValues['sign'] = $sign;
    }
    
    /**
     * 生成签名
     * @return string
     */
    private function makeSign($data, $key)
    {
        if (!is_array($data) || count($data) == 0 || empty(trim($key))) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("签名数据或Key为空");
        }
        $key = trim($key);
        ksort($data);
        $string = $this->ToUrlParams($data);
        $string = "{$string}&key={$key}";
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }
    
    /**
     * 转换为微信支付接口返回数据的数组格式
     * @param string $reponseXml
     * @return array
     */
    private function setReponseValues($reponseXml)
    {
        $this->reponseXml = $reponseXml;
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($reponseXml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $this->reponseValues = $result;
    }
    
     /**
     * 设置请求参数
     * @param string $key
     * @param string|int $value
     */
    protected function setValue($key, $value)
    {
        $this->requestValues[$key] = $value;
    }
    
    /**
     * 转换数据数据为Xml格式数据
     * @param array $values
     * @return string 返回xml字符串
     * @throws \app\framework\weixin\exceptions\WxPayApiException
     */
    protected function toXml($values)
    {
        if (!is_array($values) || count($values) == 0) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("数组数据错误");
        }
        
        $xml = "<xml>";
        foreach ($values as $key => $val) {
            if (is_numeric($val)) {
                    $xml.="<".$key.">".$val."</".$key.">";
            } else {
                    $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    
    /**
     * 生成32位随机数
     * @return type
     */
    protected function getNonce()
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ($i = 0; $i < 32; $i++) {
                $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    
    /**
     * 生成签名对象字符串
     * @return string
     */
    private function toUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
}
