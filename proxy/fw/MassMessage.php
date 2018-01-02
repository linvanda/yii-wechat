<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;

/**
 * Description of MassMessage
 *
 * @author Chenxy
 */
class MassMessage extends ApiBase
{
    /**
     * 群发图文消息
     * @param array $touser [openid1,openid2,....]
     * @param string $mediaId
     * @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function sendMpnews($touser, $mediaId)
    {
        $params = ['touser' => $touser, 'mpnews' => ['media_id' => $mediaId], 'msgtype' => 'mpnews'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/send', 'POST', '根据openid群发图文消息', $params);
        return $result;
    }
    
    /**
     * 群发图文消息给所有粉丝
     * @param string $mediaId
     * @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function sendMpnewsToAll($mediaId)
    {
        $params = ['filter' => ['is_to_all' => true], 'mpnews' => ['media_id' => $mediaId], 'msgtype' => 'mpnews'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/sendall', 'POST', '群发图文消息给所有粉丝', $params);
        return $result;
    }


    /**
     * 查询群发消息发送状态
     * @param string $msgId
     * @return object {
                "msg_id":201053012,
                "msg_status":"SEND_SUCCESS" 表示发送成功
           }
     */
    public function get($msgId)
    {
        $params = ['msg_id' => $msgId];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/get', 'POST', '查询群发消息发送状态', $params);
        return $result;
    }
    
    /**
     * 删除（撤消）群发消息
     * 请注意，只有已经发送成功的消息才能删除删除消息只是将消息的图文详情页失效，已经收到的用户，还是能在其本地看到消息卡片。 另外，删除群发消息只能删除图文消息和视频消息，其他类型的消息一经发送，无法删除。
     * @param string $msgId
     * @return object {
                "errcode":0,
                "errmsg":"ok"
             }
     */
    public function delete($msgId)
    {
        $params = ['msg_id' => $msgId];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/delete', 'POST', '删除（撤消）群发', $params);
        return $result;
    }
}
