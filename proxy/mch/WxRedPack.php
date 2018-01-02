<?php

namespace app\framework\weixin\proxy\mch;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of WxRedPack
 *
 * @author Administrator
 */
class WxRedPack extends WxPayApiBase
{
    private $_actName;
    private $_wishing;
    private $_remark;
    private $_redPackHelper;
    
     /**
     * 实例化红包对象
     * @param tystringpe $actName 活动名称
     * @param string $wishing 祝福语
     * @param string $remark 备注
     * @param  \app\framework\weixin\interfaces\IRedPackHelper
     */
    private function __construct($actName, $wishing, $remark, $IRedPackHelper)
    {
        $this->_actName = $actName;
        $this->_wishing = $wishing;
        $this->_remark = $remark;
        $this->_redPackHelper = $IRedPackHelper;
    }

    /**
     * 创建红包对象
     * @param string $actName 活动名称
     * @param string $wishing （拆后显示）
     * @param string $remark （拆前显示）
     * @param  \app\framework\weixin\interfaces\IRedPackHelper
     * @return \app\framework\weixin\proxy\mch\WxRedPack
     */
    public static function create($actName, $wishing, $remark, $IRedPackHelper)
    {
        return new self($actName, $wishing, $remark, $IRedPackHelper);
    }
    
    protected function getSignKey()
    {
        $wxMchInfo = $this->_redPackHelper->getWxMchInfo();
        return $wxMchInfo->mchKey;
    }
    
    protected function getWxPaySSLCert()
    {
        $wechatId = $this->_redPackHelper->getWxMchInfo()->originalId;
        $wxPayCert = \Yii::$container->get('app\framework\weixin\interfaces\IWxMchPayCertStore', [$wechatId]);
        return $wxPayCert;
    }
    
    /**
     * 设置红包数据
     */
    protected function setRequestValues()
    {
        // 获取商户配置
        $wxMchInfo = $this->_redPackHelper->getWxMchInfo();
        $mchId = $wxMchInfo->mchId;
        // 配置数据
        $values = [
        'nonce_str' => $this->getNonce(),
        'mch_id' => $mchId,
        'wxappid' => $wxMchInfo->appId,
        'nick_name' => \app\framework\utils\StringHelper::substr($wxMchInfo->mchName, 0, 16),
        'send_name' => \app\framework\utils\StringHelper::substr($this->_actName, 0, 32),
        'total_num' => 1,
        'wishing' => \app\framework\utils\StringHelper::substr($this->_wishing, 0, 128),
        'client_ip' => $_SERVER['REMOTE_ADDR'],
        'act_name' => \app\framework\utils\StringHelper::substr($this->_actName, 0, 32),
        'remark' => \app\framework\utils\StringHelper::substr($this->_remark, 0, 256),
        'logo_imgurl' => ''
        ];
        
        return $values;
    }

    /**
     * 获取红包订单号
     * @return string|bool false表示未生成订单号
     */
    public function getBillNo()
    {
        return $this->getValue('mch_billno');
    }

    /**
     * 设置商户logo
     * @param string $imgUrl url路径
     */
    public function setLogo($imgUrl)
    {
        $this->setValue("logo_imgurl", $imgUrl);
    }

    /**
     * 发送红包
     * @param string $openid 发送对象
     * @param float $amount 单位:元
     * @param string $billNo 订单号，发送失败时可通过该参数重入，重新发送
     * @param string $sendName 商户名称
     * @return array ['result_code' => SUCCESS|FAIL, 'errcode' => 40012程序异常 -1表示交易失败，0成功，其它为微信具体错误代码, 'errmsg' => $errmsg]
     * @throws \app\framework\weixin\exceptions\WxPayApiException
     */
    public function send($openid, $amount, $billNo = '', $sendName = '')
    {
        try {
            // 转换为分
            $amount *= 100;
            // 设置红包数据
            $this->setValue('re_openid', $openid);
            $this->setValue('total_amount', $amount);
            $this->setValue('min_value', $amount);
            $this->setValue('max_value', $amount);
            if (!empty($sendName)) {
                $this->setValue('send_name', $sendName);
            }
            // 生成商户订单号
            if (empty($billNo)) {
                $wxMchInfo = $this->_redPackHelper->getWxMchInfo();
                $mchId = $wxMchInfo->mchId;
                $billNo = $this->_redPackHelper->makeBillNo($mchId);
            }
            $this->setValue('mch_billno', $billNo);
            $this->execute("https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack");
            $this->_redPackHelper->log($this->requestXml, $this->reponseXml);
            $resultCode = ($this->reponseValues['return_code'] === 'SUCCESS' && $this->reponseValues['result_code'] === 'SUCCESS') ?
                'SUCCESS' : 'FAIL';
            $errcode = $resultCode === 'SUCCESS' ? 0 : (key_exists('result_code', $this->reponseValues)
                ? $this->reponseValues['err_code']
                : -1);
            $errmsg = $resultCode === 'FAIL' && key_exists('result_code', $this->reponseValues)
                ? $this->reponseValues['err_code_des']
                : $this->reponseValues['return_msg'];
            return ['result_code' => $resultCode, 'errcode' => $errcode, 'errmsg' => $errmsg, 'billno' => $billNo];
        } catch (\Exception $ex) {
            \Yii::error($ex);
            // 记录发送日志
            if (empty($this->requestXml)) {
                $this->requestValues = array_merge($this->setRequestValues(), $this->requestValues);
                $this->requestXml = $this->toXml($this->requestValues);
            }
            $this->_redPackHelper->log($this->requestXml, $this->reponseXml, $ex);
            return ['result_code' => 'FAIL', 'errcode' => 40012, 'errmsg' => $ex->getMessage()];
        }
    }
}
