<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;

/**
 * 模板消息相关api
 *
 * @author Chenxy
 */
class TemplateMessage extends ApiBase
{
    /**
     * 发送模板消息
     * @param string $openid
     * @param IMsgTemplate $msgTemplate
     * @param IMsgTemplateRepository $msgTemplateRepository
     * @return object {
           "errcode":0,
           "errmsg":"ok",
           "msgid":200228332
       }
     */
    public function sendMsg($openid, $msgTemplate, $msgTemplateRepository)
    {
        $templateId = $this->getTemplateId($msgTemplate->shortId, $msgTemplateRepository);
        $data = $msgTemplate->getData();
        $result = $this->send($openid, $templateId, $msgTemplate->url, $data, $msgTemplate->topColor);
        return $result;
    }
    
    /**
     * 发送模板消息
     * @param string $openid 接收者openid
     * @param string $templateId 模板消息id
     * @param string $url 详情url
     * @param array $data 模板参数定义
     * @param string $topColor 顶部颜色定义，默认：#FF0000
     * @return object {
           "errcode":0,
           "errmsg":"ok",
           "msgid":200228332
       }
     */
    public function send($openid, $templateId, $url, $data, $topColor = '#FF0000')
    {
        $params = ['touser' => $openid, 'template_id' => $templateId, 'url' => $url, 'topcolor' => $topColor, 'data' => $data];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/message/template/send', 'POST', '发送模板消息', $params);
        return $result;
    }
    
    /**
     * 获取消息模板id
     * @param string $shortTemplateId
     * @return string 模板id
     */
    public function getTemplateId($shortTemplateId, $msgTemplateRepository)
    {
        $templateId = $msgTemplateRepository->getTemplateId($shortTemplateId);
        if (!empty($templateId)) {
            return $templateId;
        }
        $params = ['template_id_short' => $shortTemplateId];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/template/api_add_template', 'POST', '获取消息模板Id', $params);
        $msgTemplateRepository->addTemplate($shortTemplateId, $result->template_id);
        return $result->template_id;
    }
    
    /**
     * 设置所属行业
     * @param string $industryId1 公众号模板消息所属行业编号(主营)
     * @param string $industryId2 公众号模板消息所属行业编号（副营）
     * @return object 行业代码请参照http://mp.weixin.qq.com/wiki/17/304c1885ea66dbedf7dc170d84999a9d.html
     */
    public function setIndustry($industryId1, $industryId2)
    {
        $params = ['industry_id1' => $industryId1, 'industry_id2' => $industryId2];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/template/api_set_industry', 'POST', '设置模板消息所属行业', $params);
        return $result;
    }
}
