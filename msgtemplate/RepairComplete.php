<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\msgtemplate;

/**
 * 报修完成消息模板
 *
 * @author Chenxy
 */
class RepairComplete implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM206026150';

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

    private $_first = ['value' => "您的最新报修进度如下：", 'color' => '#000000'];
    private $_remark = ['value' => "", 'color' => '#000000'];
    
    public function __construct($contentNote, $progressNote, $url = '')
    {
        $this->contentNote = $contentNote;
        $this->progressNote = $progressNote;
        $this->url = $url;
        $this->wrap();
    }
    
    private function wrap()
    {
        $this->_first['value'] = $this->_first['value'] . chr(10);
        $this->_remark['value'] = chr(10) . "您的本次报修已处理完成！" . chr(10) . chr(10) ."对本次服务满意吗？去评价一下吧~";
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
