<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msg;

/**
 * 接收微信消息处理基类，所有要处理微信消息的要继承此类
 *
 * @author Chenxy
 */
abstract class BaseHandler
{
     /**
     * 用户自定义数据，该数据会与微信数据包进行合并
     * @var array
     */
    protected $userData;
    
    /**
     * 用户自定义数据
     * @param array $userData
     */
    public function __construct($userData = [])
    {
        $this->userData = $userData;
    }
    
    /**
     * 声明该handler能够处理事件类型
     */
    abstract public function getHandlers();
    
    /**
     * 调用每个handle前处理的，合并用户和微信的数据，基类可根据需要重写
     * @param array $data 带有handler标识的微信数据包
     * @return array 用户数据+微信数据包
     */
    public function beforeHandle($data)
    {
        return array_merge($data, $this->userData);
    }
    
    /**
     * 回复消息，最大支持10条，超过会截断
     * @param string $contentType 内容类型，支持中英文
     * @param mixed $contentData 内容，参考微信接口不同的类型传入不同
     * 文本类：string 一段文字
     * 图片：string media_id值
     * 语音: string media_id值
     * 视频：array ['MediaId'=>MediaId,Title=>标题(非必填),Description=>描述(非必填)]
     * 音乐：array ['ThumbMediaId'=>缩略图的媒体id,Title=>标题(非必填),Description=>描述(非必填),MusicUrl=>音乐链接(非必填),HQMusicUrl=>高质量音乐链接(非必填)]
     * 图文：array [['Title'=>标题(非必填),'Description'=>描述(非必填),'PicUrl'=>图片链接，支持JPG、PNG(非必填),'Url'=>点击图文消息跳转链接(非必填)],,,]
     * @return array
     * @throws \yii\base\NotSupportedException
     */
    protected function reply($contentType, $contentData)
    {
        switch ($contentType) {
            case '文字':
            case 'text':
                $contentData = ['contentType'=>'text', 'content'=>$contentData];
                break;
            case '图片':
            case 'image':
                $contentData = ['contentType'=>'image', 'content'=>$contentData];
                break;
            case '语音':
            case 'voice':
                $contentData = ['contentType'=>'voice', 'content'=>$contentData];
                break;
            case '视频':
            case 'video':
                $contentData = ['contentType'=>'video', 'content'=>$contentData];
                break;
            case '音乐':
            case 'music':
                $contentData = ['contentType'=>'music', 'content'=>$contentData];
                break;
            case '图文':
            case 'news':
                $contentData = ['contentType'=>'news', 'content'=>$contentData];
                break;
            default:
                throw new \yii\base\NotSupportedException("不支持的回复类型:{$contentType},有效值[文字、text、图片、image、语音、voice、视频、video、音乐、music、图文、news]");
        }
        
        return $contentData;
    }
}
