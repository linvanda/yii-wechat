<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 15:09
 */

namespace app\framework\weixin\qy\models\msg;

/**
 * 文本消息
 * Class TextMessage
 * @package app\framework\weixin\qy\models\msg
 */
class FileMessage extends AbstractMessage
{
    public $file;

    /**
     * @param $mediaId
     * @param string $touser
     * @param string $agentid
     * @param string $toparty
     * @param string $totag
     * @param string $safe
     */
    public function __construct($mediaId, $touser = '', $agentid = '', $toparty = '', $totag = '', $safe = '0')
    {
        $this->file = ['media_id' => $mediaId];
        parent::__construct('file', $agentid, $touser, $toparty, $totag, $safe);
    }
}
