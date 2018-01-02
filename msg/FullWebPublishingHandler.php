<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

/**
 * 支持全网发布验证，需要全网发布时请通过messageProcessor的install方法加载
 *
 * @author chenxy
 */
class FullWebPublishingHandler extends BaseHandler
{
    /**
     * 声明该handler能够处理事件类型
     * @return array
     */
    public function getHandlers()
    {
        return ["testEvent", "testMsg" , "testApi", "testTicket"];
    }
    
    /**
     * 全网发布模拟事件验证:模拟粉丝触发专用测试公众号的事件
     * @param type $data
     * @return type
     */
    public function testEvent($data)
    {
        $content =  $data['Event'] . "from_callback";
        return $this->reply("text", $content);
    }
    
    /**
     * 全网发布模拟消息验证:模拟粉丝发送文本消息给专用测试公众号
     * @param type $data
     * @return type
     */
    public function testMsg($data)
    {
        $content =  $data['Content'] . "_callback";
        return $this->reply("text", $content);
    }
    
    /**
     * 全网发布模拟API验证:模拟粉丝发送文本消息给专用测试公众号
     * @param array $data
     * @return string
     */
    public function testApi($data)
    {
        $queryAuthCode = str_replace("QUERY_AUTH_CODE:", "", $data['Content']);
        $apiProxy = new \app\framework\weixin\proxy\component\WxComponent();
        $result = $apiProxy->queryAuth($queryAuthCode);
        $accessToken = $result->authorization_info->authorizer_access_token;
        $params = ['touser' => $data['FromUserName'], 'msgtype' => 'text', 'text' => ['content' => "{$queryAuthCode}_from_api"]];
        // 调用客服消息接口发送数据
        $restClient = new \app\framework\webService\RestClientHelper();
        $restClient->invoke("https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$accessToken}", $params, "POST", false);
        return "";
    }
    
    /**
     * 全网发布模拟推送component_verify_ticket给开发者
     * @param array $data
     * @return string
     */
    public function testTicket($data)
    {
        return "success";
    }
}
