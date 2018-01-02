<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

/**
 * 临时素材相关接口
 *
 * @author Chenxy
 */
class Media extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 获取临时素材
     * @param string $mediaId 媒体文件ID
     * @return string 文件内容，调用方应使用fwrite写入到本地
     */
    public function get($mediaId)
    {
        $params =['media_id' => $mediaId];
        $fileContent = $this->execute('https://api.weixin.qq.com/cgi-bin/media/get', 'GET', '获取临时素材', $params, true, false);
        return $fileContent;
    }
    
    /**
     * 获取临时素材文件，包括头部信息
     * @param $mediaId
     * @return mixed
     */
    public function getMediaInfo($mediaId)
    {
        $access_token_param_name = $this->_accessTokenHelper->getAccessTokenParamName();
        $accessToken = $this->_accessTokenHelper->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?media_id={$mediaId}&{$access_token_param_name}={$accessToken}";
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        $httpInfo = curl_getinfo($curl);
        curl_close($curl);
        return [$httpInfo, $res];
    }
    
    /**
     * 新增临时素材
     * @param string $type 分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param string $file 素材文件本地路径，如果是url路径请先自行保存到本地再调用本接口
     * @return object {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     */
    public function upload($type, $file)
    {
        $file = realpath($file);
        $allowTypes = ['image','voice','video','thumb'];
        if (!in_array($type, $allowTypes)) {
            throw new \InvalidArgumentException("不支持的素材上传类型{$type},允许的类型值" . implode('、', $allowTypes));
        }
        
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("文件不存在");
        }
        
        $size = filesize($file);
        switch ($type) {
            case 'image':
                $maxSize = 2 * 1024 * 1024;
                break;
            case 'voice':
                $maxSize = 5 * 1024 * 1024;
                break;
            case 'video':
                $maxSize = 10 * 1024 * 1024;
                break;
            case 'thumb':
                $maxSize = 64 * 1024;
                break;
            default :
                break;
        }
        
        if ($size > $maxSize) {
            throw new \Exception("上传素材文件大小超出限制,图片2M，音频5M，视频10M，缩略图64K");
        }
        // 不能直接调用基类的exceute方法
        $extend = pathinfo($file);
        $finfo = new \finfo(FILEINFO_MIME);
        $mime= $finfo->file($file);
        // 校验图片文件
        $fileType = substr($mime, 0, strpos($mime, ';'));
        if ($type == 'image' && !in_array(strtolower($fileType), ["image/bmp", "image/png", "image/jpeg", "image/jpg", "image/gif"])) {
            throw new \Exception("不是有效的图片文件");
        }
        $data = ['name' => $extend["filename"], 'file' => new \CURLFile($file), 'type'=>$mime];
        $ch = curl_init();
        // 设置最大10分钟要上传完成
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $access_token_param_name = $this->_accessTokenHelper->getAccessTokenParamName();
        $access_token = $this->_accessTokenHelper->getAccessToken();
        $occurError = false;
        // 调用微信接口
        for ($i = 0; $i < $this->_maxInvokeTimes; $i++) {
            $url="https://api.weixin.qq.com/cgi-bin/media/upload?{$access_token_param_name}={$access_token}&type={$type}";
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            // 返回成功
            if (strpos($result, '{"errcode":') === false || strpos($result, '{"errcode":0') === 0) {
                    $occurError = false;
                    break;
            }
            // 失败重试
            $occurError = true;
            $errorResult = json_decode($result);
            $this->_accessTokenHelper->makeExpire($errorResult->errcode);
            if ($this->_accessTokenHelper->checkIsExpired($errorResult->errcode)) {
                $access_token = $this->_accessTokenHelper->getAccessToken();
                $i--;
            }
        }
        
        if ($occurError) {
            throw new \app\framework\weixin\WeixinException("新增临时素材失败，错误码:" . $errorResult->errcode . ' 消息:' . $errorResult->errmsg);
        }
        
        return json_decode($result);
    }
    
    /**
     * 上传图文消息中的图片
     * @param string $file 本地路径或url
     * @return object
     * {
        "url":  "http://mmbiz.qpic.cn/mmbiz/gLO17UPS6FS2xsypf378iaNhWacZ1G1UplZYWEYfwvuU6Ont96b1roYs CNFwaRrSaKTPCUdBK9DgEHicsKwWCBRQ/0"
        }
     * @throws \Exception
     * @throws \InvalidArgumentException
     * @throws \app\framework\weixin\WeixinException
     */
    public function uploadImg($file)
    {
        $isUrl = (stripos($file, "http") === 0);
        // 传入的是url则下载到本地
        if ($isUrl) {
            $url = $file;
            $downFile = $_SERVER ['DOCUMENT_ROOT'] . "/temp/weixin/" . \app\framework\utils\StringHelper::uuid() . substr($url, strrpos($url, ".")); 
            if (!copy($url, $downFile)) {
                throw new \Exception("无法下载文件{$file}");
            }
            $file = $downFile;
        }
        
        $file = realpath($file);
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("文件不存在");
        }
        
        if (filesize($file) > 1 * 1024 * 1024) {
            throw new \Exception("图文内容中的图片大小不能超过1M");
        }
        // 不能直接调用基类的exceute方法
        $extend = pathinfo($file);
        $finfo = new \finfo(FILEINFO_MIME);
        $mime= $finfo->file($file);
        // 校验图片文件
        $fileType = substr($mime, 0, strpos($mime, ';'));
        if (!in_array(strtolower($fileType), ["image/bmp", "image/png", "image/jpeg", "image/jpg", "image/gif"])) {
            throw new \Exception("不是有效的图片文件");
        }
        $data = ['name' => $extend["filename"], 'file' => new \CURLFile($file), 'type'=>$mime];
        $ch = curl_init();
        // 设置最大10分钟要上传完成
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $access_token_param_name = $this->_accessTokenHelper->getAccessTokenParamName();
        $access_token = $this->_accessTokenHelper->getAccessToken();
        $occurError = false;
        // 调用微信接口
        for ($i = 0; $i < $this->_maxInvokeTimes; $i++) {
            $url="https://api.weixin.qq.com/cgi-bin/media/uploadimg?{$access_token_param_name}={$access_token}&type={$type}";
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            // 返回成功
            if (strpos($result, '{"errcode":') === false || strpos($result, '{"errcode":0') === 0) {
                    $occurError = false;
                    break;
            }
            // 失败重试
            $occurError = true;
            $errorResult = json_decode($result);
            $this->_accessTokenHelper->makeExpire($errorResult->errcode);
            if ($this->_accessTokenHelper->checkIsExpired($errorResult->errcode)) {
                $access_token = $this->_accessTokenHelper->getAccessToken();
                $i--;
            }
        }
        
        if ($occurError) {
            throw new \app\framework\weixin\WeixinException("上传图文消息中的图片失败，错误码:" . $errorResult->errcode . ' 消息:' . $errorResult->errmsg);
        }
        
        // 删除下载临时文件
        if ($isUrl) {
            is_file($file) && unlink($file);
        }
        return json_decode($result);
    }
}
