<?php
/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 11:50
 */

namespace app\framework\weixin\payment;

use app\framework\utils\ArrayHelper;
use app\framework\weixin\component\utils\MapHelper;
use app\framework\weixin\interfaces\INotify;
use app\framework\weixin\interfaces\ISign;
use app\framework\weixin\component\utils\XmlHelper;
use Exception;

class WxNotify implements INotify
{
    /** @var array $notifyData */
    protected $notifyData;
    /** @var array $excludeFields */
    protected static $excludeFields = [
        'public_id', 'order_no', 'notify_sign'
    ];

    /**
     * 获取数据
     * @return string
     */
    protected function pickNotiy() {
        return file_get_contents("php://input");
    }

    public function receive($notify = '')
    {
        $notify = empty($notify) ? $this->pickNotiy() : $notify;
        if (empty($notify)) {
            throw new Exception('数据未空或者不符合规则');
        }

        $this->notifyData = XmlHelper::parseXml($notify);
        return $this->notifyData;
    }

    public function verifySign($signClassName, $key) {
        $params = ArrayHelper::removeBatch($this->notifyData, static::$excludeFields);

        /** @var ISign $sign */
        $sign = MapHelper::instance($signClassName, [$params, $key]);

        if (isset($params['sign'])) {
            return $sign->verify($params['sign']);
        }

        return false;
    }


    public function response($result = true, $message = '')
    {
        header('Content-Type:text/xml');
        echo XmlHelper::toXml([
            'return_code' => $result ? 'SUCCESS' : 'FAIL',
            'return_msg' => $message
        ]);
    }

}