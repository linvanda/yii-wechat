<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 服务预约进度消息模板
 *
 * @author Lvq
 */
class AppointmentProgress implements IMsgTemplate
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
     * 顶部颜色  '#FF0000'
     * @var string
     */
    public $topColor = '#FF0000';

    /**
     * 开始
     * @var string
     */
    public $first;

    /**
     * 内容
     * @var string
     */
    public $content;

    /**
     * 最新进度
     * @var string
     */
    public $progress;

    /**
     * 订单编号
     * @var string
     */
    public $orderno;

    /**
     * 安排
     * @var string
     */
    public $arrange;

    /**
     * 预约
     * @var string
     */
    public $booking;


    /**
     * 备注
     * @var string
     */
    public $remark;

    private $_first = ['value' => '', 'color' => '#000000'];
    private $_remark = ['value' => '', 'color' => '#000000'];

    public function __construct($first, $content, $progress, $orderno, $arrange, $booking, $remark, $url)
    {
        $this->url = $url;
        $this->first = $first;
        $this->content = $content;
        $this->progress = $progress;
        $this->orderno = $orderno;
        $this->arrange = $arrange;
        $this->booking = $booking;
        $this->remark = $remark;
        $this->wrap();
    }

    private function wrap()
    {
        $this->_first['value'] = $this->first . chr(10);
        $this->_remark['value'] = "安排人员：".$this->arrange.chr(10)."预约时间：".$this->booking.chr(10).$this->remark ;
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->content, 'color' => '#000000'],
            'keyword2' => ['value' => $this->progress, 'color' => '#009900'],
            'keyword3' => ['value' => $this->orderno, 'color' => '#000000'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}