<?php

namespace app\framework\weixin\msgtemplate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of DbMsgTemplateRepository
 *
 * @author Chenxy
 */
class DbMsgTemplateRepository implements \app\framework\weixin\interfaces\IMsgTemplateRepository
{
    private $_tenantCode;
    private $_accountId;
    private $_dbConnection;
    
    public function __construct($tenantCode, $accountId)
    {
        $this->_tenantCode = $tenantCode;
        $this->_accountId = $accountId;
        $this->_dbConnection = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn($tenantCode);
    }
    
    /**
     * 根据短号获取长号
     * @param string $shortTemplateId
     * @return string 无记录返回''
     */
    public function getTemplateId($shortTemplateId)
    {
        $templateId = $this->_dbConnection->createCommand(
            "select template_id from p_template_msg_setting where account_id=:account_id and template_id_short=:template_id_short and is_deleted=0",
            [':account_id'=> $this->_accountId, ':template_id_short'=>$shortTemplateId]
        )->queryScalar();
        
        return $templateId ?: '';
    }
     
    /**
     * 新增消息模板
     * @param string $shortTemplateId
     * @param string $templateId
     * @return integer 1成功，0失败
     */
    public function addTemplate($shortTemplateId, $templateId)
    {
        $now = \app\framework\utils\DateTimeHelper::now();
        return $this->_dbConnection->createCommand()->insert(
            'p_template_msg_setting',
            ['id' => \app\framework\utils\StringHelper::uuid(),
            'template_id_short' => $shortTemplateId,
            'template_id' => $templateId,
            'account_id' => $this->_accountId,
            'created_on' => $now,
            'modified_on' => $now,
            'is_deleted' => 0]
        )->execute();
    }
    
    /**
     * 根据短号删除模板
     * @param string $shortTemplateId
     */
    public function deleteTemplate($shortTemplateId)
    {
        $this->_dbConnection->createCommand()->delete(
            'p_template_msg_setting',
            ['template_id_short' => $shortTemplateId,
            'account_id' => $this->_accountId]
        )->execute();
    }
}
