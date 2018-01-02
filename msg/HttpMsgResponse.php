<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

/**
 * Description of HttpMsgResponse
 *
 * @property array $responseData The response data.
 * @property string $responseXml The response xml data
 * @author chenxy
 */
class HttpMsgResponse extends \yii\base\Object
{
    /**
     * 响应的数据的数组格式
     * @var array
     */
    public $_responseData;
    
    /**
     * 响应内容
     * @var string
     */
    public $_responseXml;
    
    /**
     * 构造方法
     * @param string|array $responseData
     */
    public function __construct($responseData)
    {
        $this->_responseData = $responseData;
    }
    
    /**
     * 获取响应数据某个具体值
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getResponseValue($name, $defaultValue = null)
    {
        $responseData = $this->getResposeData();
        return isset($responseData[$name]) ? $params[$name] : $defaultValue;
    }

    /**
     * 获取响应数据
     * @return array
     */
    public function getResponseData()
    {
        return $this->_responseData;
    }
    
    /**
     * 设置响应数据
     * @param string|array $value
     */
    public function setResponseData($value)
    {
        $this->_responseData = $value;
    }

    /**
     * 设置响应数据
     * @param string $value
     */
    public function setResponseXml($value)
    {
        $this->_responseXml = $value;
    }
    
    /**
     * 获取响应数据
     */
    public function getResponseXml()
    {
        if (isset($this->_responseXml)) {
            return $this->_responseXml;
        }
        
        if (is_string($this->_responseData)) {
            $this->_responseXml = $this->_responseData;
        } else {
            $xml = $this->data2xml($this->_responseData);
            $this->_responseXml = $xml->asXML();
        }
        
        return $this->_responseXml;
    }

    /**
     * 向客户端发出响应内容
     */
    public function send()
    {
        exit($this->_responseXml);
    }
    
    private function data2xml($data, $xmlNode = null, $tag = 'item')
    {
        if (is_null($xmlNode)) {
            $xmlNode = new \SimpleXMLElement('<xml></xml>');
        }
        foreach ($data as $key => $value) {
            // 内嵌数组无key时，即[,,,]使用指定的tag
            is_numeric($key) && $key = $tag;
            // 值为数组或对象类型
            if (is_array($value) || is_object($value)) {
                $child = $xmlNode->addChild($key);
                $this->data2xml($value, $child, $tag);
            } elseif (is_numeric($value)) {
                $child = $xmlNode->addChild($key, $value);
            } else {
                $child = $xmlNode->addChild($key);
                $node  = dom_import_simplexml($child);
                $node->appendChild($node->ownerDocument->createCDATASection($value));
            }
        }
        
        return $xmlNode;
    }
}
