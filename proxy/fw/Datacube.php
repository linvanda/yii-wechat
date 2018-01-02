<?php
/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/2/16
 * Time: 18:11
 */

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

/**
 * 数据统计接口
 * Class Datacube
 * @package app\framework\weixin\proxy\fw
 */
class Datacube extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }

    public function get($type, $beginDate, $endDate) {
        $accessToken = $this->_accessTokenHelper->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/{$type}?access_token={$accessToken}";
        $params =[
            'begin_date' => $beginDate,
            'end_date' => $endDate,
        ];
        $result = $this->execute($url, 'POST', '创建自定义菜单', $params);
        return $result;
    }
}