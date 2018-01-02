<?php

namespace app\framework\weixin\msg;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 消息类处理基类，业务处理继承该类
 * @author Chenxy
 */
class BaseMessageHandler extends BaseHandler
{
    /**
     * 声明该handler能够处理事件类型
     * @return array
     */
    public function getHandlers()
    {
        return ['text',
                'image',
                'voice',
                'video',
                'shortvideo',
                'location',
                'link',
                'qualification_verify_success'];
    }
    
    /**
     * 文本消息：用户输入一段文本
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'text','Content'=>'文本消息内容',MsgId=>消息id] + userData
     * @return string
     */
    public function text($data)
    {
        return '';
    }
    
    /**
     * 图片消息：用户发送一张图片
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'image','PicUrl'=>'图片链接','MediaId'=>图片消息媒体id, 'MsgId'=>消息id] + userData
     * @return string
     */
    public function image($data)
    {
        return '';
    }
    
    /**
     * 语音消息：用户发送一段语音
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'voice','Format'=>'语音格式，如amr','MediaId'=>语音消息媒体id, 'MsgId'=>消息id] + userData
     * @return string
     */
    public function voice($data)
    {
        return '';
    }
    
    /**
     * 视频消息:用户发送一段视频
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'video','ThumbMediaId'=>'视频消息缩略图的媒体id','MediaId'=>语音消息媒体id, 'MsgId'=>消息id] + userData
     * @return string
     */
    public function video($data)
    {
        return '';
    }
    
    /**
     * 小视频消息:用户录制发送一段小视频
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'shortvideo','ThumbMediaId'=>'视频消息缩略图的媒体id','MediaId'=>语音消息媒体id, 'MsgId'=>消息id] + userData
     * @return string
     */
    public function shortvideo($data)
    {
        return '';
    }
    
    /**
     * 位置消息:用户发送一个位置
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'location','Location_X'=>'纬度','Location_Y'=>经度, 'Scale'=>'缩放大小','Label'=>地理位置信息,'MsgId'=>消息id] + userData
     * @return string
     */
    public function location($data)
    {
        return '';
    }
   
    /**
     * 链接消息：用户发送一个链接
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'link','Title'=>'消息标题','Description'=>消息描述, 'Url'=>'消息链接','MsgId'=>消息id] + userData
     * @return string
     */
    public function link($data)
    {
        return '';
    }
    
    public function qualification_verify_success($data)
    {
        return '';
    }
}
