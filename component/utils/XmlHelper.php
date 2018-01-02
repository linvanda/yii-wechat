<?php

/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 16:55
 */

namespace app\framework\weixin\component\utils;

use Exception;

class XmlHelper
{
    /**
     * 转换为微信支付接口返回数据的数组格式
     * @param string $reponseXml
     * @return array
     */
    public static function parseXml($xml)
    {
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $result;
    }

    /**
     * 转换数据数据为Xml格式数据
     * @param $values $values
     * @return string 返回xml字符串
     * @throws Exception
     */
    public static function toXml($values)
    {
        if (!is_array($values) || count($values) == 0) {
            throw new Exception("数组数据错误");
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

}