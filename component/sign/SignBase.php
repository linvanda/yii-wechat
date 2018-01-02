<?php
/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 16:35
 */

namespace app\framework\weixin\component\sign;

use app\framework\weixin\exceptions\WxPaymentException;

class SignBase
{
    protected $excludeFields = [];
    /**
     * 生成签名
     * @return string
     */
    protected function make($params, $key)
    {
        $key = trim($key);
        $params = preg_grep('/.+/', $params);
        if (!is_array($params) || count($params) == 0 || empty($key)) {
            throw new WxPaymentException("签名数据或Key为空");
        }
        ksort($params);
        $stringSignTemp = sprintf('%s&key=%s', http_build_query($params), $key);
        $signValue = strtoupper(md5($stringSignTemp));
        return $signValue;
    }

}