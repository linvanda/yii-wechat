<?php

namespace app\framework\weixin\msgtemplate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 提交订单成功通知模板
 *
 * @author Zhaock
 */
class ShopOrderResult implements IMsgTemplate {

    const TEMPLATE_NO = 'OPENTM207344430';

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
     * 用户名
     * @var string 
     */
    public $userName;

    /**
     * 订单号
     * @var string
     */
    public $orderNo;

    /**
     * 订单金额
     * @var string
     */
    public $pay;

    /**
     * 下单时间
     * @var string 
     */
    public $orderTime;

    /**
     * 支付方式
     * @var string
     */
    public $paymentType;

    /**
     * 备注
     * @var string 
     */
    public $remark;
    public $first;

    public function __construct($first, $userName, $orderNo, $pay, $orderTime, $paymentType, $remark, $url = '') {
        $this->first = $first;
        $this->userName = $userName;
        $this->orderNo = $orderNo;
        $this->pay = $pay;
        $this->orderTime = $orderTime;
        $this->paymentType = $paymentType;
        $this->remark = $remark;
        $this->url = $url;
    }

    public function getData() {
        $data = [
            'first' => ['value' => $this->first, 'color' => '#000000'],
            'keyword1' => ['value' => $this->userName, 'color' => '#000000'],
            'keyword2' => ['value' => $this->orderNo, 'color' => '#000000'],
            'keyword3' => ['value' => $this->pay, 'color' => '#0080ff'],
            'keyword4' => ['value' => $this->orderTime, 'color' => '#000000'],
            'keyword5' => ['value' => $this->paymentType, 'color' => '#000000'],
            'remark' => ['value' => $this->remark, 'color' => '#000000'],
        ];

        return $data;
    }
}
