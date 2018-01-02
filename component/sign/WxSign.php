<?php

/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 16:34
 */

namespace app\framework\weixin\component\sign;

use app\framework\weixin\interfaces\ISign;
use app\framework\weixin\exceptions\WxPaymentException;

class WxSign extends SignBase implements ISign
{
    protected $excludeFields = ['sign', 'sign_type'];
    protected $params;
    protected $key;
    protected $sign;

    public function __construct($params = [], $key = '')
    {
        $this->params = $params;
        $this->key = $key;

        $this->initSign();
    }

    /**
     * 初始化
     * @throws WxPaymentException
     */
    protected function initSign() {
        foreach($this->excludeFields as $field) {
            if (isset($this->params[$field])) {
                unset($this->params[$field]);
            }
        }

        $this->sign = $this->make($this->params, $this->key);
    }

    /**
     * 生成签名
     * @return string
     */
    public function generate()
    {
        return isset($this->sign) ? $this->sign : '';
    }

    /**
     * 验证签名
     * @param $sign
     * @return bool
     */
    public function verify($sign)
    {
        return $this->sign == $sign;
    }
}