<?php

namespace app\framework\weixin\proxy\fw;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 获取微信用户相关接口
 *
 * @author Chenxy
 */
use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

class User extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 根据openid获取用户信息
     * @param string $openid
     * @return {
                "subscribe": 1,
                "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
                "nickname": "Band",
                "sex": 1,
                "language": "zh_CN",
                "city": "广州",
                "province": "广东",
                "country": "中国",
                "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
               "subscribe_time": 1382694957,
               "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
            }
     */
    public function info($openid)
    {
        $params =['openid' => $openid];
        $userInfo = $this->execute('https://api.weixin.qq.com/cgi-bin/user/info', 'GET', "通过openid获取用户信息", $params, true, false);
        $data = json_decode($userInfo);

        // city、province这几个字段返回值存在一些ASCII码，会导致json_decode结果为null， 目前只发现这一种情况导致json_decode结果为null
        if ($data === null){
            $data = $this->_dealWxUserJsonData($userInfo, array($params));
        }
        return $data;
    }

    /**
     * 批量获取用户信息，一次最多100个
     * @param $openIds 格式：['openid1','openid2',...]
     * @return mixed
     */
    public function batchGetInfo($openIds)
    {
        if (!is_array($openIds) || empty($openIds)) {
            return [];
        }

        $cnt = 0;
        $arr = [];
        foreach ($openIds as $openId) {
            if ($cnt++ > 100) {
                break;
            }

            $arr[] = ['openid'=>$openId];//不要加 'lang'=>'zh-CN'，否则拿到的是英文的
        }

        $userInfo = $this->execute('https://api.weixin.qq.com/cgi-bin/user/info/batchget', 'POST', "批量获取用户信息", ['user_list'=>$arr], true, false);
        $data = json_decode($userInfo);

        // city、province这几个字段返回值存在一些ASCII码，会导致json_decode结果为null， 目前只发现这一种情况导致json_decode结果为null
        if ($data === null){
            $data = $this->_dealWxUserJsonData($userInfo, $arr);
        }
        return $data;
    }
    
    /**
     * 获取用户列表
     * @param string $nextOpenId 第一个拉取的OPENID，不填默认从头开始拉取
     * @return object {"total":2,"count":2,"data":{"openid":["","OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
     */
    public function get($nextOpenId = '')
    {
        $params =['next_openid' => $nextOpenId];
        $userList = $this->execute('https://api.weixin.qq.com/cgi-bin/user/get', 'GET', '获取用户列表', $params);
        return $userList;
    }

    /**
     * 处理微信用户数据接口返回的json字符串存在ascii码
     * @param string $data
     * @param array $openIdArray
     * @return mixed|null
     */
    private function _dealWxUserJsonData($data, $openIdArray){

        if(empty($data)){
            return $data;
        }

        $json_data = null;
        try{
            preg_match_all('/city":"(.*)","province":"(.*)","country":"(.*)"/U', $data, $reg_arr);
            preg_match_all('/nickname":"(.*)"/U', $data, $reg_arr_nick_name);
            preg_match_all('/remark":"(.*)"/U', $data, $reg_arr_remark);

            if (!empty($reg_arr) and !empty($reg_arr_nick_name)){
                $reg_arr[] = $reg_arr_nick_name[1];
            }

            if (!empty($reg_arr) and !empty($reg_arr_remark)){
                $reg_arr[] = $reg_arr_remark[1];
            }

            if (!empty($reg_arr)){
                foreach($reg_arr[1] as $city_value){
                    $city = htmlentities(bin2hex($city_value));
                    $data = str_replace('"city":"' . $city_value.'"', '"city": "' . $city .'"', $data);
                }

                foreach($reg_arr[2] as $province_value){
                    $province = htmlentities(bin2hex($province_value));
                    $data = str_replace('"province":"' . $province_value.'"', '"province": "' . $province .'"', $data);
                }

                foreach($reg_arr[3] as $country_value){
                    $country = htmlentities(bin2hex($country_value));
                    $data = str_replace('"country":"' . $country_value.'"', '"country": "' . $country .'"', $data);
                }

                foreach($reg_arr[4] as $nickname_value){
                    $nickname = htmlentities(bin2hex($nickname_value));
                    $data = str_replace('"nickname":"' . $nickname_value.'"', '"nickname": "' . $nickname .'"', $data);
                }

                foreach($reg_arr[5] as $remark_value){
                    $remark = htmlentities(bin2hex($remark_value));
                    $data = str_replace('"remark":"' . $remark_value.'"', '"remark": "' . $remark .'"', $data);
                }

                $json_data = json_decode($data);

                if(!empty($json_data) && isset($json_data->user_info_list)){
                    foreach($json_data->user_info_list as &$row){
                        $row->city = hex2bin(html_entity_decode($row->city));  //转换十六进制字符串为二进制字符串，将原先的值替换回来
                        $row->province = hex2bin(html_entity_decode($row->province));
                        $row->country = hex2bin(html_entity_decode($row->country));
                        $row->nickname = hex2bin(html_entity_decode($row->nickname));
                        $row->remark = hex2bin(html_entity_decode($row->remark));
                    }
                }elseif(!empty($json_data)){
                    $json_data->city = hex2bin(html_entity_decode($json_data->city));
                    $json_data->province = hex2bin(html_entity_decode($json_data->province));
                    $json_data->country = hex2bin(html_entity_decode($json_data->country));
                    $json_data->nickname = hex2bin(html_entity_decode($json_data->nickname));
                    $json_data->remark = hex2bin(html_entity_decode($json_data->remark));
                }
            }
        }catch (\Exception $e) {
            $openidArr = [];
            foreach($openIdArray as $row){
                $openidArr[] = $row['openid'];
            }
            \Yii::error("微信openId（" . implode(', ', $openidArr) . "），处理微信会员经json_decode()因数据存在ascii码而结果为NULL的情况下，发生以下错误：" . $e->getMessage());
        }

        return $json_data;
    }
}
