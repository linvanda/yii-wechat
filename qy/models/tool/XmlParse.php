<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * User: wangl10
 * Date: 2015/7/31
 * Time: 18:22
 */

namespace app\framework\weixin\qy\models\tool;

class XmlParse
{

    /**
     * 提取出xml数据包中的加密消息
     * @param string $xmlText 待提取的xml字符串
     * @return string 提取出的加密消息字符串
     */
    public static function extract($xmlText)
    {
        try {
            $xml = new \DOMDocument();
            $xml->loadXML($xmlText);
            $array_e = $xml->getElementsByTagName('Encrypt');
            $array_a = $xml->getElementsByTagName('ToUserName');
            $encrypt = $array_e->item(0)->nodeValue;
            $toUserName = $array_a->item(0)->nodeValue;
            return [0, $encrypt, $toUserName];
        } catch (\Exception $e) {
            return [ErrorCode::$ParseXmlError, null, null];
        }
    }

    /**
     * 生成xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @return string
     */
    public static function generate($encrypt, $signature, $timestamp, $nonce)
    {
        $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    /**
     * 创建XML
     * @param $data
     * @return string
     */
    public static function toXml($data)
    {
        $format = '<?xml version="1.0"?><xml>%s</xml>';
        return sprintf($format, static::createXmlNodeStr($data));
    }

    /**
     * 创建xml节点字符串
     * @param $data
     * @return string
     */
    private static function createXmlNodeStr($data)
    {
        if (is_array($data)) {
            $tempXml = '';
            foreach ($data as $key => $value) {
                $tempXml .= '<' . $key . '>' . static::createXmlNodeStr($value) . '</' . $key . '>';
            }
            return $tempXml;
        }
        if (is_string($data)) {
            return '<![CDATA[' . $data . ']]>';
        }
        if (is_object($data)) {
            $ref = new \ReflectionClass($data);
            $props = $ref->getProperties();
            $tmpData = [];
            foreach ($props as $prop) {
                $prop->setAccessible(true);
                $tmpData[$prop->getName()] = $prop->getValue($data);
                $prop->setAccessible(false);
            }
            $data = $tmpData;
            return static::createXmlNodeStr($data);
        }
        return $data;
    }

    /**
     * 将xml转换为Array
     * @param $xml
     * @return array|string
     */
    public static function toArray($xml)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $root = $doc->documentElement;
        $output = static::domNodeToArray($root);
        $output['@root'] = $root->tagName;
        return $output;
    }

    /**
     * 将Dom节点转换为Array
     * @param $node
     * @return array|string
     */
    private static function domNodeToArray($node)
    {
        $output = [];
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = static::domNodeToArray($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset($output[$t])) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } elseif ($v || $v === '0') {
                        $output = (string)$v;
                    }
                }
                if ($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
                    $output = ['@content' => $output]; //Change output into an array.
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = [];
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string)$attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }
}
