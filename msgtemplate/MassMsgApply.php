<?php

namespace app\framework\weixin\msgtemplate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 公众号群发消息模板
 *
 * @author Chenxy
 */
class MassMsgApply implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM401253428';
    /**
     * 模板短编号
     * @var string
     */
    public $shortId = self::TEMPLATE_NO;
    
    /**
     * 详情url
     * @var string 
     */
    public $url = '';

    /**
     * 内容
     * @var string
     */
    public $content = '';

    /**
     * 时间
     * @var string
     */
    public $dateTime = '';
    
    /**
     * 顶部颜色  '#FF0000' 
     * @var string 
     */
    public $topColor = '#FF0000';

    /**
     * 公众号
     * @var string
     */
    public $public_account;
    
    private $_first = ['value' => "有群发消息需要您审核：", 'color' => '#FA2347'];
    private $_remark = ['value' => "点击详情，确认是否允许本次群发。30分钟未操作则默认拒绝群发", 'color' => '#000000'];
    
    public function __construct($public_account, $url, $content, $dateTime)
    {
        $this->public_account = $public_account;
        $this->url = $url;
        $this->content = $content;
        $this->dateTime = $dateTime;
        $this->wrap();
    }
    
    private function wrap()
    {
         $this->_first['value'] = $this->_first['value'] . chr(10);
    }
    
    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => '公众号群发消息审批', 'color' => '#000000'],
            'keyword2' => ['value' => $this->content, 'color' => '#000000'],
            'keyword3' => ['value' => $this->dateTime, 'color' => '#000000'],
            'remark' => $this->_remark
        ];
        
        return $data;
    }
}
