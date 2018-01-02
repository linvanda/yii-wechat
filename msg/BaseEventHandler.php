<?php

namespace app\framework\weixin\msg;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 事件推送类处理基类，业务处理继承该类
 * @author Chenxy
 */
class BaseEventHandler extends BaseHandler
{
    /**
     * 声明该handler能够处理事件类型
     * @return array
     */
    public function getHandlers()
    {
        return ['subscribe',
                'unsubscribe',
                'scan',
                'location',
                'click',
                'view',
                'masssendjobfinish',
                'templatesendjobfinish',
                'kf_create_session',
                'kf_close_session',
                'kf_switch_session',
                'device_text',
                'naming_verify_success',
                'naming_verify_fail',
                'qualification_verify_success',
                'qualification_verify_fail',
                'annual_renew',
                'verify_expired'];
    }
    
    /**
     * 关注事件
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'event','Event'=>'subscribe'] + userData
     * 若二维码扫描关注还包括以下参数 'EventKey' =>事件KEY值，qrscene_为前缀，后面为二维码的参数值, 'Ticket'=>二维码的ticket，可用来换取二维码图片
     * @return string
     */
    public function subscribe($data)
    {
        return '';
    }
    
    /**
     * 取消关注
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'event','Event'=>'unsubscribe'] + userData
     * @return string
     */
    public function unsubscribe($data)
    {
        return '';
    }
    
    /**
     * 已关注二维码扫描
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'event','Event'=>'scan','EventKey' =>事件KEY值，qrscene_为前缀，后面为二维码的参数值, 'Ticket'=>二维码的ticket，可用来换取二维码图片] + userData
     * @return string
     */
    public function scan($data)
    {
        return '';
    }
    
    /**
     * 上报地理位置
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'event','Event'=>'location','Latitude' =>地理位置纬度, 'Longitude'=>地理位置经度 ,'Precision'=>地理位置精度] + userData
     * @return string
     */
    public function location($data)
    {
        return '';
    }
    
    /**
     * 点击菜单
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'event','Event'=>'click', 'EventKey'=>事件KEY值] + userData
     * @return string
     */
    public function click($data)
    {
        return '';
    }
    
    /**
     * 菜单url链接
     * @param array $data 事件接收的xml数据的数组格式['ToUserName'=>开发者微信号,'FromUserName'=>OpenID,'CreateTime'=>消息创建时间戳,'MsgType'=>'event','Event'=>'view', 'EventKey'=>设置的跳转URL] + userData
     * @return string
     */
    public function view($data)
    {
        return '';
    }
    
    /**
     * 群发消息即将完成时的推送事件
     * @param type $data
     * @return string
     */
    public function masssendjobfinish($data)
    {
        return '';
    }
    
    /**
     * 模版消息发送任务完成后的推送事件
     * @param type $data
     */
    public function templatesendjobfinish($data)
    {
        return '';
    }

    /**
     * 多客服创建会话
     * @param type $data
     */
    public function kf_create_session($data)
    {
        return '';
    }

    /**
     * 多客服关闭会话
     * @param type $data
     */
    public function kf_close_session($data)
    {
        return '';
    }

    /**
     * 多客服转接会话
     * @param type $data
     */
    public function kf_switch_session($data)
    {
        return '';
    }
    
    public function device_text($data)
    {
        return '';
    }
    
    /**
     * 名称认证成功
     * @param type $data
     * @return string
     */
    public function naming_verify_success($data)
    {
        return '';
    }
    
    /**
     * 名称认证失败
     * @param type $data
     * @return string
     */
    public function naming_verify_fail($data)
    {
        return '';
    }
    
    /**
     * 资质认证成功
     * @param type $data
     * @return string
     */
    public function qualification_verify_success($data)
    {
        return '';
    }
    
    /**
     * 资质认证失败
     * @param type $data
     * @return string
     */
    public function qualification_verify_fail($data)
    {
        return '';
    }
    
    /**
     * 年审通知
     * @param type $data
     */
    public function annual_renew($data)
    {
        return '';
    }
    
    /**
     * 认证过期失效
     * @param type $data
     */
    public function verify_expired($data)
    {
        return '';
    }
}
