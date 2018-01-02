<?php

namespace app\framework\weixin\interfaces;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 消息模板仓储接口
 * @author Chenxy
 */
interface IMsgTemplateRepository
{
    /**
     * 根据模板短号获取模板id
     * @param string $shortTemplateId 模板短号
     */
    public function getTemplateId($shortTemplateId);

    /**
     * 添加模板到本地仓储
     * @param string $shortTemplateId 模板短号
     * @param string $templateId 模板id
     */
    public function addTemplate($shortTemplateId, $templateId);
    
    /**
     * 根据短号删除模板
     * @param string $shortTemplateId
     */
    public function deleteTemplate($shortTemplateId);
}
