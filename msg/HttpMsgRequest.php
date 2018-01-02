<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

/**
 * Description of HttpMsgRequest
 *
 * @property array $queryParams The request GET parameter values.
 * @property string $postXml Description This property is read-only.
 * @property string $requestXml The request xml.
 * @property array $requestData The request data.
 * @property bool $isFullWebPublishing 是否全网发布请求.
 * @author chenxy
 */
class HttpMsgRequest extends \yii\base\Object
{
    /**
     * 原始xml字符串
     * @var type
     */
    private $_postXml;
    
    /**
     * url参数
     * @var array
     */
    private $_queryParams;
    
    /**
     * 请求的xml字符串
     * @var string
     */
    private $_requestXml;
    
    /**
     * 请求数据的数组格式
     * @var array
     */
    private $_requestData;

    
    private $_isFullWebPublising;
    
    /**
     * 构造方法
     * @param string $postXml
     */
    public function __construct($postXml)
    {
        $this->_postXml = $postXml;
        $this->_requestXml = $postXml;
    }

    /**
     * 获取某个Url参数值
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getQueryParam($name, $defaultValue = null)
    {
        $params = $this->getQueryParams();

        return isset($params[$name]) ? $params[$name] : $defaultValue;
    }
    
    /**
     * 获取Url参数
     * @return array
     */
    public function getQueryParams()
    {
        if (isset($this->_queryParams)) {
            return $this->_queryParams;
        }

        return $_GET;
    }
    
    /**
     * 设置url参数
     * @param array $values
     */
    public function setQueryParams($values)
    {
        $this->_queryParams = $values;
    }
    
    /**
     * 获取请求的原始的xml字符串
     * @return string
     */
    public function getPostXml()
    {
        return $this->_postXml;
    }
    
    /**
     * 获取请求的xml字符串
     * @return string
     */
    public function getRequestXml()
    {
        return $this->_requestXml;
    }
    
    /**
     * 设置请求的xml字符串
     * @param string $value
     */
    public function setRequestXml($value)
    {
        $this->_requestXml = $value;
    }
    
    /**
     * 获取请求的数据
     * @return array
     */
    public function getRequestData()
    {
        return $this->_requestData;
    }
    
    /**
     * 设置请求的数据
     * @param array $value
     */
    public function setRequestData($value)
    {
        $this->_requestData = $value;
    }

    /**
     * 是否全网发布验证
     * @return bool
     */
    public function getIsFullWebPublishing()
    {
        if (!isset($this->_isFullWebPublising)) {
            $xml_tree = new \DOMDocument();
            $xml_tree->loadXML($this->_requestXml);
            $appIdNodes = $xml_tree->getElementsByTagName('AuthorizerAppid');
            $toUsrNameNodes = $xml_tree->getElementsByTagName('ToUserName');
            $appId = $appIdNodes->length == 0 ? '' : $appIdNodes->item(0)->nodeValue;
            $toUsrName = $toUsrNameNodes->length == 0 ? '' : $toUsrNameNodes->item(0)->nodeValue;
            $this->_isFullWebPublising = ($toUsrName === "gh_3c884a361561" || $appId === "wx570bc396a51b8ff8");
        }
        
        return $this->_isFullWebPublising;
    }
}
