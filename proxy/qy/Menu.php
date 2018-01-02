<?php

/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/6/13
 * Time: 15:09
 */

namespace app\framework\weixin\proxy\qy;

use app\framework\weixin\interfaces\IAccessTokenHelper;
use app\framework\weixin\proxy\ApiBase;

class Menu extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }

    /**
     * 创建自定义菜单
     * @param $buttons
     * @param $agentId
     * @return object
     * @throws \app\framework\weixin\WeixinException
     */
    public function create($buttons, $agentId)
    {
        $params = ['button' => $buttons];
        $result = $this->execute(('https://qyapi.weixin.qq.com/cgi-bin/menu/create?agentid=' . $agentId), 'POST', '创建自定义菜单', $params);
        return $result;
    }

    /**
     * 删除自定义菜单
     * @param $agentId
     * @return object
     * @throws \app\framework\weixin\WeixinException
     */
    public function delete($agentId)
    {
        $params = [];
        $result = $this->execute(('https://qyapi.weixin.qq.com/cgi-bin/menu/delete?agentid=' . $agentId), 'GET', '删除自定义菜单', $params);
        return $result;
    }

    /**
     * 查询自定义菜单
     * @param $agentId
     * @return object
     * @throws \app\framework\weixin\WeixinException
     */
    public function get($agentId)
    {
        $params = [];
        $buttons = $this->execute(('https://qyapi.weixin.qq.com/cgi-bin/menu/get?agentid=' . $agentId), 'GET', '查询自定义菜单', $params);
        return $buttons;
    }
}
