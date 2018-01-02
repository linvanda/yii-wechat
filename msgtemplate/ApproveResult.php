<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 审批结果消息模板
 *
 * @author Lvq
 */
class ApproveResult implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM206928270';
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
     * 审核项目
     * @var string
     */
    public $project;

    /**
     * 备注
     * @var string
     */
    public $remark;

    /**
     * 审核结果颜色
     * @var string
     */
    public $color;

    /**
     * 审核结果
     * @var string
     */
    public $result;

    private $_first = ['value' => '', 'color' => '#000000'];
    private $_remark = ['value' => '', 'color' => '#000000'];

    public function __construct($project, $result, $resultColor, $remark, $url)
    {
        $this->url = $url;
        $this->project = $project;
        $this->result = $result;
        $this->remark = $remark;
        $this->color = $resultColor;
        $this->wrap();
    }

    private function wrap()
    {
        $this->_remark['value'] = $this->remark . chr(10);
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->project, 'color' => '#000000'],
            'keyword2' => ['value' => $this->result, 'color' => $this->color],
            'remark' => $this->_remark
        ];

        return $data;
    }
}