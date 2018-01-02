<?php
/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 16:20
 */

namespace app\framework\weixin\interfaces;

interface INotify
{
    /**
     * @param string $notify 获取的数据
     * @return array
     */
    public function receive($notify = '');

    /**
     * 验证签名
     * @param string $signClassName
     * @param string $key
     * @return bool
     */
    public function verifySign($signClassName, $key);

    /**
     * @param bool $result
     * @param string $message
     * @return string
     */
    public function response($result = true, $message = '');
}