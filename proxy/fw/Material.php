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
 * 素材相关接口
 *
 * @author Chenxy
 */
class Material extends ApiBase
{
    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }
    
    /**
     * 新增永久图文素材
     * @param type $articles 图文数组
     * @return object ｛media_id�?
     */
    public function addNews($articles)
    {
        $params =['articles' => $articles];
        $mediaInfo = $this->execute('https://api.weixin.qq.com/cgi-bin/material/add_news', 'POST', '新增永久图文素材', $params);
        return $mediaInfo;
    }
    
    /**
     * 新增非图文类型永久素
     * @param string $type 分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param string $file 素材文件本地路径，如果是url路径请先自行保存到本地再调用本接口
     * @param string $title 上传视频时才有效
     * @param string $introduction 上传视频时才有效
     * @return object
     */
    public function addMaterial($type, $file, $title = '', $introduction = '')
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
        $finfo = new \finfo(FILEINFO_MIME);
        $mime= $finfo->file($file);
        $fileType = substr($mime, 0, strpos($mime, ';'));
        // 校验图片文件
        if ($type == 'image' && !in_array(strtolower($fileType), ["image/bmp", "image/png", "image/jpeg", "image/jpg", "image/gif"])) {
            throw new \Exception("不是有效的图片文件");
        }
        
        //$data = ['name' => $extend["filename"], 'media' => new \CURLFile($file), 'type'=>$mime];
        $media=new \CURLFile($file);
        $media->setPostFilename(basename($file));
        $data = ['media' => $media, 'type'=>$mime];
        if ($type == 'video') {
            $data['description'] = json_encode(['title'=>$title ,'introduction'=>$introduction]);
        }
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
            $url="https://api.weixin.qq.com/cgi-bin/material/add_material?{$access_token_param_name}={$access_token}&type={$type}";
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
            throw new \app\framework\weixin\WeixinException("新增其他类型永久素材失败，错误码:" . $errorResult->errcode . ' 消息:' . $errorResult->errmsg);
        }
        
        return json_decode($result);
    }
    /**
     * 获取视频类永久素
     * @param string $mediaId
     * @return object {
                    "title":TITLE,
                    "description":DESCRIPTION,
                    "down_url":DOWN_URL,
                  }
     */
    public function getVideo($mediaId)
    {
        $params =['media_id' => $mediaId];
        $mediaInfo = $this->execute('https://api.weixin.qq.com/cgi-bin/material/get_material', 'POST', '获取视频类永久素材', $params);
        return $mediaInfo;
    }


    /**
     * 获取图文永久素材
     * @param string $mediaId
     * @return object {
                        "news_item":
                        [
                            {
                            "title":TITLE,
                            "thumb_media_id"::THUMB_MEDIA_ID,
                            "show_cover_pic":SHOW_COVER_PIC(0/1),
                            "author":AUTHOR,
                            "digest":DIGEST,
                            "content":CONTENT,
                            "url":URL,
                            "content_source_url":CONTENT_SOURCE_URL
                            },
                            //多图文消息有多篇文章
                         ]
                       }
     */
    public function getNews($mediaId)
    {
        $params =['media_id' => $mediaId];
        $news = $this->execute('https://api.weixin.qq.com/cgi-bin/material/get_material', 'POST', '获取图文永久素材', $params);
        return $news;
    }

    /**
     * 获取语音、图片、缩略图等永久素材
     * @param string $mediaId
     * @return string 文件内容，调用方应使用fwrite写入到本地
     */
    public function getMaterial($mediaId)
    {
        $params =['media_id' => $mediaId];
        $fileContent = $this->execute('https://api.weixin.qq.com/cgi-bin/material/get_material', 'POST', '获取语音、图片、缩略图永久素材', $params, true, false);
        return $fileContent;
    }
    
    /**
     * 删除永久素材
     * @param string $mediaId
     * @return type
     */
    public function delMaterial($mediaId)
    {
        $params =['media_id' => $mediaId];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/material/del_material', 'POST', '删除永久素材', $params);
        return $result;
    }
    
    /**
     * 修改永久图文素材
     * @param string $mediaId
     * @param int $index
     * @param Articel $article
     * @return type
     */
    public function updateNews($mediaId, $index, $article)
    {
        $params =['media_id' => $mediaId, 'index' => $index, 'articles' => $article];
        $result = $this->execute('https://api.weixin.qq.com/cgi-bin/material/update_news', 'POST', '修改永久图文素材', $params);
        return $result;
    }
    
    /**
     * 获取素材总数
     * @return type
     */
    public function getMaterialCount()
    {
        $params =[];
        $summary = $this->execute('https://api.weixin.qq.com/cgi-bin/material/get_materialcount', 'GET', '获取素材总数', $params);
        return $summary;
    }
    
    /**
     * 获取素材列表
     * @param string $type 图片（image）、视频（video）、�语音（voice）、�图文（news）
     * @param int $offset 从全部素材的该偏移位置开始返回，0表示从第1个素材返回
     * @param int $count 返回素材的数量，取值在1到20之间
     * @return type
     */
    public function batchGetMaterial($type, $offset, $count)
    {
        $params =['type' => $type, 'offset' => $offset, 'count' => $count];
        $list = $this->execute('https://api.weixin.qq.com/cgi-bin/material/batchget_material', 'POST', '获取素材列表', $params);
        return $list;
    }
}
