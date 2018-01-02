<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 会所预订通知模板
 *
 * @author kongy
 */
class ClubBookNotify implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM401559123';
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
     * 开始
     * @var string
     */
    public $first;

    /**
     * 开始
     * @var string
     */
    public $service;

    /**
     * 开始
     * @var string
     */
    public $status;

    /**
     * 订单编号
     * @var string
     */
    public $order_no;

    /**
     * 备注
     * @var string
     */
    public $remark;

    private $_first = ['value' => '', 'color' => '#000000'];
    private $_remark = ['value' => '', 'color' => '#000000'];

    public function __construct($first, $service, $status, $order_no, $remark)
    {
        $this->first = $first;
        $this->service = $service;
        $this->status = $status;
        $this->order_no = $order_no;
        $this->remark = $remark;
        $this->wrap();
    }

    private function wrap()
    {
        $this->_first['value'] = $this->first;
        $this->_remark['value'] = $this->remark;
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->service, 'color' => '#000000'],
            'keyword2' => ['value' => $this->status, 'color' => '#000000'],
            'keyword3' => ['value' => $this->order_no, 'color' => '#000000'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}