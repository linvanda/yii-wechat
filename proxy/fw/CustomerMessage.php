<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;

/**
 * 微信客服消息
 *
 * @author Chenxy
 */
class CustomerMessage extends ApiBase
{
    /**
     * 发送文本客服消息
     * @param string $openid
     * @param string $text
     * @return object {
                    "errcode" : 0,
                    "errmsg" : "ok",
               }
     */
    public function sendText($openid, $text)
    {
        $this->validateOpenId($openid);
        // 转换换行符
        $text = str_replace('access_token', 'my_access_token', str_replace('<br>', chr(10), $text));
        $params = ['touser' => $openid, 'msgtype' => 'text', 'text' => ['content' => $text]];
        $result = $this->execute("https://api.weixin.qq.com/cgi-bin/message/custom/send", "POST", "发送文本客服消息", $params);
        return $result;
    }
    
    /**
     * 发送图片客服消息
     * @param string $openid
     * @param string $mediaId
     * @return object {
                    "errcode" : 0,
                    "errmsg" : "ok",
               }
     */
    public function sendImage($openid, $mediaId)
    {
        $this->validateOpenId($openid);
        $params = ['touser' => $openid, 'msgtype' => 'image', 'image' => ['media_id' => $mediaId]];
        $result = $this->execute("https://api.weixin.qq.com/cgi-bin/message/custom/send", "POST", "发送图片客服消息", $params);
        return $result;
    }
    
    /**
     * 发送文件客服消息
     * @param string $openid
     * @param string $mediaId
     * @return object {
                    "errcode" : 0,
                    "errmsg" : "ok",
               }
     */
    public function sendFile($openid, $mediaId)
    {
        $this->validateOpenId($openid);
        $params = ['touser' => $openid, 'msgtype' => 'file', 'file' => ['media_id' => $mediaId]];
        $result = $this->execute("https://api.weixin.qq.com/cgi-bin/message/custom/send", "POST", "发送文件客服消息", $params);
        return $result;
    }

    /**
     * 发送语音客服消息
     * @param string $openid
     * @param string $mediaId
     * @return object {
        "errcode" : 0,
        "errmsg" : "ok",
        }
     */
    public function sendVoice($openid, $mediaId){
        $this->validateOpenId($openid);
        $params = ['touser' => $openid, 'msgtype' => 'voice', 'voice' => ['media_id' => $mediaId]];
        $result = $this->execute("https://api.weixin.qq.com/cgi-bin/message/custom/send", "POST", "发送语音客服消息", $params);
        return $result;
    }

    /**
     * 发送图文消息
     * @param string $openid
     * @param string $articles
     * @return object {
    "errcode" : 0,
    "errmsg" : "ok",
    }
     */
    public function sendNews($openid, $articles)
    {
        $this->validateOpenId($openid);
        $params = ['touser' => $openid, 'msgtype' => 'news', 'news' => ['articles' => $articles]];
        $result = $this->execute("https://api.weixin.qq.com/cgi-bin/message/custom/send", "POST", "发送文件客服消息", $params);
        return $result;
    }

    /**
     * 校验openid合法性
     * @param string $openid
     * @throws \InvalidArgumentException
     */
    private function validateOpenId($openid)
    {
        if (!is_string($openid)) {
            $msg = (is_array($openid) || is_object($openid))
                    ? json_encode($openid, JSON_UNESCAPED_UNICODE)
                    : strval($openid);
            throw new \InvalidArgumentException("发送客服消息时参数openid不是字符串类型,值：{$msg}");
        }
    }
}
