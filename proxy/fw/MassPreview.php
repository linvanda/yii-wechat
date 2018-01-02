<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;

/**
 * 群发预览接口
 *
 * @author Chenxy
 */
class MassPreview extends ApiBase
{
    /**
     * 预览群发图文
     * @param string $touser openid 要发送预览的用户
     * @param string $mediaId 永久图文media_id
     * @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function mpnews($touser, $mediaId)
    {
        $params = ['touser' => $touser, 'mpnews' => ['media_id' => $mediaId], 'msgtype' => 'mpnews'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/preview', 'POST', '预览群发图文', $params);
        return $result;
    }
    
    /**
     * 预览群发文本
     * @param string $touser openid 要发送预览的用户
     * @param string $text 文本内容
     * @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function text($touser, $text)
    {
        $params = ['touser' => $touser, 'text' => ['content' => $text], 'msgtype' => 'text'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/preview', 'POST', '预览群发文本', $params);
        return $result;
    }
    
    /**
     * 预览群发语音
     * @param string $touser openid 要发送预览的用户
     * @param string $mediaId 永久语音media_id
     *  @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function voice($touser, $mediaId)
    {
        $params = ['touser' => $touser, 'voice' => ['media_id' => $mediaId], 'msgtype' => 'voice'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/preview', 'POST', '预览群发语音', $params);
        return $result;
    }
    
    /**
     * 预览群发图片
     * @param string $touser openid 要发送预览的用户
     * @param string $mediaId 永久图片素材media_id
     *  @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function image($touser, $mediaId)
    {
        $params = ['touser' => $touser, 'image' => ['media_id' => $mediaId], 'msgtype' => 'image'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/preview', 'POST', '预览群发图片', $params);
        return $result;
    }
    
    /**
     * 预览群发视频
     * @param string $touser openid 要发送预览的用户
     * @param string $mediaId 永久视频素材media_id
     *  @return object {
            "errcode":0,
            "errmsg":"send job submission success",
            "msg_id":34182
         }
     */
    public function video($touser, $mediaId)
    {
        $params = ['touser' => $touser, 'mpvideo' => ['media_id' => $mediaId], 'msgtype' => 'mpvideo'];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/mass/preview', 'POST', '预览群发视频', $params);
        return $result;
    }
}
