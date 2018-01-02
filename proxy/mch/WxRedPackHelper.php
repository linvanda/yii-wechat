<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\mch;

/**
 * Description of WxRedPackHelper
 *
 * @author chenxy
 */
class WxRedPackHelper implements \app\framework\weixin\interfaces\IRedPackHelper
{
    private $_orgCode;
    
    private $_accountId;
    
    private $_orgDbConnection;
    
    public function __construct($accountId, $orgCode = '')
    {
        if (empty($orgCode)) {
            $orgReader = \Yii::$container->get('app\framework\biz\tenant\TenantReaderInterface');
            $orgCode = $orgReader->getCurrentTenantCode();
        }
        $this->_orgCode = $orgCode;
        $this->_orgDbConnection = \app\framework\biz\cache\OrganizationCacheManager::getTenantDbConn($this->_orgCode);
        $this->_accountId = $accountId;
    }

    /**
     * 获取商户配置
     * @return \app\framework\weixin\WxInvoker
     */
    public function getWxMchInfo()
    {
        $wechatId = $this->_orgDbConnection->createCommand("select original_id from p_account where id = :accountId", [':accountId' => $this->_accountId])->queryScalar();
        if ($wechatId === false) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("找不到公众号:" . $this->_accountId);
        }
        
        $accessTokenRepository = \Yii::$container->get('app\framework\weixin\interfaces\IAccessTokenRepository');
        $wxInvoker = $accessTokenRepository->getWxInvoker($wechatId);
        if (empty($wxInvoker->mchId)
            || empty($wxInvoker->mchKey)
            || empty($wxInvoker->mchSSLCert)
            || empty($wxInvoker->mchSSLKey)) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("未配置支付商户,请检查配置或清除缓存重试");
        }
        
        return $wxInvoker;
    }

    /**
     * 写发放日志
     * @param string $requestXml 请求数据xml格式
     * @param string $reponseXml 响应数据
     * @param \Exception $exception 异常对象
     */
    public function log($requestXml, $reponseXml, $exception = null)
    {
        // 转换成数组
        try {
            $requestData = json_decode(json_encode(simplexml_load_string($requestXml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $ex) {
            \Yii::error($ex);
            $requestData = [];
            $exception = isset($exception) ? $exception : $ex;
        }
        
        try {
            $reponseData = empty($reponseXml)
                        ? []
                        : json_decode(json_encode(simplexml_load_string($reponseXml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $ex) {
            \Yii::error($ex);
            $reponseData = [];
            $exception = isset($exception) ? $exception : $ex;
        }
        
        // 抽取关键字段
        $now = \app\framework\utils\DateTimeHelper::now();
        $logRow = [
             'id' => \app\framework\utils\StringHelper::uuid()
            ,'account_id' => $this->_accountId
            ,'openid' => key_exists('re_openid', $requestData) ? $requestData['re_openid'] : ''
            ,'mch_bill_no' => key_exists('mch_billno', $requestData) ? $requestData['mch_billno'] : ''
            ,'amount' => (key_exists('total_amount', $requestData) ? $requestData['total_amount'] : 0)/100
            ,'original_request_xml' => $requestXml
            ,'original_reponse_xml' => $reponseXml
            ,'send_time' => $now
            ,'created_on' => $now
            ,'modified_on' => $now
            ,'is_deleted' => 0
        ];

        // 发送失败
        if (count($reponseData) == 0 || isset($exception)) {
            $logRow['status'] = "发送失败";
            $logRow['error_msg'] = isset($exception) ? $exception->getMessage() : '';
        } elseif ($reponseData['return_code'] !== "SUCCESS" || $reponseData['result_code'] !== "SUCCESS") {
            $logRow['status'] = "发送失败";
            $logRow['error_msg'] = key_exists('result_code', $reponseData)
                    ? ($reponseData['err_code'] . $reponseData['err_code_des'])
                    : $reponseData['return_msg'];
        } else {
            $logRow['status'] = "发送成功";
        }

        $this->_orgDbConnection->createCommand()->insert('p_redpack_log', $logRow)->execute();
        return;
    }
    
    /**
     * 生成商户订单号
     * @param type $mchId
     * @return string
     */
    public function makeBillNo($mchId)
    {
        try {
            $high = time() - mktime(0, 0, 0);
            $high = $this->fillZero($high, 5);
            $low = [];
            for ($i=0; $i<5; $i++) {
                $low[] = strval(mt_rand(0, 9));
            }
            $billNo = $mchId . date('Ymd') . $high . implode('', $low);
//            $low = mt_rand(1,99999);
//            $low = $this->fillZero($low,5);
//            $billNo = $mchId . date('Ymd') . $high . $low;
            return $billNo;
        } catch (\Exception $ex) {
            throw new \app\framework\weixin\exceptions\WxPayApiException("生成红包订单号异常" . $ex->getMessage());
        }
    }

    private function fillZero($int, $length)
    {
        if (strlen(strval($int)) >= $length) {
            return strval($int);
        }
        
        $fillBit = $length - strlen(strval($int));
        $arr = [];
        for ($i = 0; $i < $fillBit; $i++) {
            $arr[] = '0';
        }
        
        return implode($arr) . strval($int);
    }
}
