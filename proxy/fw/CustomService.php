<?php

namespace app\framework\weixin\proxy\fw;

/**
 * 多客服信息
 *
 * @author Lvq
 */
use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

class CustomService extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }

    /**
     * 获取多客服基本信息列表
     * @return object
     */
    public function getKfList()
    {
        $params =[];
        $info = $this->execute('https://api.weixin.qq.com/cgi-bin/customservice/getkflist', 'GET', "客服信息列表", $params);
        return $info;
    }

    /**
     * 获取多客服在线情况
     * @return object
     */
    public function getOnlineKfList()
    {
        $params =[];
        $info = $this->execute('https://api.weixin.qq.com/cgi-bin/customservice/getonlinekflist', 'GET', "客服在线情况", $params);
        return $info;
    }
}
