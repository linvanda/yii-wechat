<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 11:55
 */

namespace app\framework\weixin\qy\models\msg;

/**
 * Class AbstractMessage
 */
abstract class AbstractMessage
{
    /**
     * 消息接收者
     * 成员ID列表（消息接收者，多个接收者用‘|’分隔，最多支持1000个）。特殊情况：指定为@all，则向关注该企业应用的全部成员发送
     * @var string
     */
    public $touser;

    /**
     * 部门ID列表
     * 多个接收者用‘|’分隔，最多支持100个。当touser为@all时忽略本参数
     * @var string
     */
    public $toparty;

    /**
     * 标签ID列表
     * 多个接收者用‘|’分隔。当touser为@all时忽略本参数
     * @var string
     */
    public $totag;

    /**
     * 消息类型
     * @var string
     */
    public $msgtype;

    /**
     * 企业应用Id
     * @var string
     */
    public $agentid;

    /**
     * 表示是否是保密消息，0表示否，1表示是，默认0
     * @var string
     */
    public $safe;

    /**
     * @param $msgtype
     * @param $agentid
     * @param string $touser
     * @param string $toparty
     * @param string $totag
     * @param string $msgtype
     * @param string $safe
     */
    public function __construct($msgtype, $agentid, $touser = '', $toparty = '', $totag = '', $safe = '0')
    {
        $this->touser = $touser;
        $this->toparty = $toparty;
        $this->totag = $totag;
        $this->msgtype = $msgtype;
        $this->agentid = $agentid;
        $this->safe = $safe;
    }
}
