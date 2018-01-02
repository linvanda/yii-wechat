<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

/**
 * 长链接转短链接
 *
 * @author Chenxy
 */
use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

class Shorturl extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 获取短链接
     * @param string $longUrl 长链接
     * @return object {"errcode":0,"errmsg":"ok","short_url":"http:\/\/w.url.cn\/s\/AvCo6Ih"}
     */
    public function get($longUrl)
    {
        try {
            $params =['action' => 'long2short', 'long_url' => $longUrl];
            $urlInfo = $this->execute('https://api.weixin.qq.com/cgi-bin/shorturl', 'POST', "长链接转短链接", $params);
            return $urlInfo;
        } catch(\Exception $ex) {
            //如果微信接口报错则转为百度短网址接口实现转换
            $shortUrl = $this->getShortUrlByDwz($longUrl);
            $arr = [];
            $arr["short_url"] = $shortUrl;
            return (Object)$arr;
        }

    }

    public function getShortUrlByDwz($longUrl)
    {
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,"http://dwz.cn/create.php");
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $data=array('url'=>$longUrl);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        $strRes=curl_exec($ch);
        curl_close($ch);
        $arrResponse=json_decode($strRes,true);
        if($arrResponse['status']!=0)
        {
            throw new \Exception("百度短网址接口错误：".$arrResponse['err_msg']);
        }
        /** tinyurl */
        return $arrResponse['tinyurl'];
    }
}
