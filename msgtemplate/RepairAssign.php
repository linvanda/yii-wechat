<?php

namespace app\framework\weixin\msgtemplate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 报修已分配工程师未预约上门时间消息模板
 *
 * @author Chenxy
 */
class RepairAssign implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM206019347';
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
     * 顶部颜色  '#FF0000' 
     * @var string 
     */
    public $topColor = '#FF0000';
    
    /**
     * 内容
     * @var string
     */
    public $contentNote;
    
    /**
     * 进度
     * @var string
     */
    public $progressNote;
    
    /**
     * 安排
     * @var string
     */
    public $arrangeNote;
    
    /**
     * 预约
     * @var string
     */
    public $bookingNote;
    
    private $_first = ['value' => "您的最新报修进度如下：", 'color' => '#000000'];
    private $_remark = ['value' => "", 'color' => '#FA2347'];
    
    public function __construct($contentNote, $progressNote, $arrangeNote, $bookingNote, $url = '')
    {
        $this->arrangeNote = $arrangeNote;
        $this->bookingNote = $bookingNote;
        $this->contentNote = $contentNote;
        $this->progressNote = $progressNote;
        $this->url = $url;
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
            'keyword1' => ['value' => $this->contentNote, 'color' => '#000000'],
            'keyword2' => ['value' => $this->progressNote, 'color' => '#3AC754'],
            'keyword3' => ['value' => $this->arrangeNote, 'color' => '#000000'],
            'keyword4' => ['value' => $this->bookingNote, 'color' => '#000000'],
            'remark' => $this->_remark
        ];
        
        return $data;
    }
}