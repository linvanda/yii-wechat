<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 图文结构
 *
 * @author Chenxy
 */
class Article
{
    /**
     * 标题
     * @var string
     */
    public $title;
    
    /**
     * 图文消息的封面图片素材id（必须是永久mediaID）
     * @var string
     */
    public $thumb_media_id;
    
    /**
     * 作者
     * @var string
     */
    public $author;
    
    /**
     * 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空
     * @var string
     */
    public $digest;
    
    /**
     * 是否显示封面，0为false，即不显示，1为true，即显示
     * @var int
     */
    public $show_cover_pic;
    
    /**
     * 图文消息的具体内容，支持HTML标签，必须少于2万字符，小于1M，且此处会去除JS
     * @var string 
     */
    public $content;
    
    /**
     * 图文消息的原文地址，即点击“阅读原文”后的URL
     * @var string
     */
    public $content_source_url;
}
