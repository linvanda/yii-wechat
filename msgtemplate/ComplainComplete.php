<?php

namespace app\framework\weixin\msgtemplate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 投诉已回复模板
 *
 * @author Chenxy
 */
class ComplainComplete implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM206046796';
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
     * 回复内容
     * @var string
     */
    public $remark;
    /**
     * 进度
     * @var string
     */
    public $progressNote;
    
    private $_first = ['value' => "", 'color' => '#000000'];
    private $_remark = ['value' => "回复：" , 'color' => '#000000'];
    
    public function __construct($contentNote, $progressNote, $remark, $url = '')
    {
        $this->contentNote = $contentNote;
        $this->progressNote = $progressNote;
        $this->remark = $remark;
        $this->url = $url;
        $this->wrap();
    }
    
    private function wrap()
    {
         $this->_remark['value'] = $this->_remark['value'] . "您的投诉建议已处理完毕" . chr(10) . chr(10) ."对本次服务满意吗？去评价一下吧~";
    }
    
    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->contentNote, 'color' => '#000000'],
            'keyword2' => ['value' => $this->progressNote, 'color' => '#3AC754'],
            'remark' => $this->_remark
        ];
        
        return $data;
    }
}