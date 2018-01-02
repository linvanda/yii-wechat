<?php
/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 16:27
 */

namespace app\framework\weixin\interfaces;


interface ISign
{
    /**
     * 获取签名
     * @return string
     */
    public function generate();

    /**
     * 校验签名
     * @param $sign
     * @return bool
     */
    public function verify($sign);
}