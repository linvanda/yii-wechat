<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 面积补差提醒消息模板
 *
 * @author kongy
 */
class AreaBalanceRemind implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM207463837';
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
     * 前言
     * @var string
     */
    public $first;

    /**
     * 房号
     * @var string
     */
    public $roomName;

    /**
     * 客户姓名
     * @var string
     */
    public $customerName;

    /**
     * 收费金额
     * @var string
     */
    public $payAmount;

    /**
     * 费用说明
     * @var string
     */
    public $feeDesc;

    /*
     * 备注
     */
    public $remark;

    public function __construct($first, $payAmount, $feeDesc, $customerName, $roomName, $remark, $url)
    {
        $this->url = $url;
        $this->first = $first;
        $this->payAmount = $payAmount;
        $this->feeDesc = $feeDesc;
        $this->customerName = $customerName;
        $this->roomName = $roomName;
        $this->remark = $remark;
    }

    public function getData()
    {
        $data = [
            'first' => ['value' => $this->first, 'color' => '#000000'],
            'keyword1' => ['value' => $this->payAmount, 'color' => '#000000'],
            'keyword2' => ['value' => $this->feeDesc, 'color' => '#000000'],
            'keyword3' => ['value' => $this->customerName, 'color' => '#000000'],
            'keyword4' => ['value' => $this->roomName, 'color' => '#000000'],
            'remark' => ['value' => $this->remark, 'color' => '#000000'],
        ];

        return $data;
    }
}
