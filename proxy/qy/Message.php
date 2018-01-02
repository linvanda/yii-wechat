<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\qy;

/**
 * Description of Message
 *
 * @author Chenxy
 */
use app\framework\weixin\interfaces\IAccessTokenHelper;
use app\framework\weixin\proxy\ApiBase;

class Message extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 发送普通文本消息
     * @param string $touser 成员id列表，多个用|分隔
     * @param string $content 消息内容
     * @param string $agentid 企业应用的id
     * @return object {
                    "errcode": 0,
                    "errmsg": "ok",
                    "invaliduser": "UserID1",
                    "invalidparty":"PartyID1",
                    "invalidtag":"TagID1"
                 }
     */
    public function sendText($touser, $content, $agentid)
    {
        $msgContent = ['content' => $content];
        $data =['touser' => $touser, 'msgtype' => 'text', 'agentid' => $agentid, 'text' => $msgContent];
        $sendResult = $this->execute('https://qyapi.weixin.qq.com/cgi-bin/message/send', 'POST', '发送文本消息', $data);
        return $sendResult;
    }
}
