<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 活动参与确认提醒消息模板
 */
class ActivityConfirm implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM202425424';
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
     * 报名人
     * @var string
     */
    public $name;

    /**
     * 手机号码
     * @var string
     */
    public $mobile;

    /**
     * 参加人数
     * @var string
     */
    public $number;

    /**
     * 活动时间
     * @var string
     */
    public $time;

    private $_remark = ['value' => '记得按时参加哦，不见不散！', 'color' => '#000000'];

    public function __construct($first, $name, $mobile, $number, $time, $url)
    {
        $this->url = $url;
        $this->first = $first;
        $this->name = $name;
        $this->mobile = $mobile;
        $this->number = $number;
        $this->time = $time;
        $this->wrap();
    }

    private function wrap()
    {
        $this->_remark['value'] = $this->_remark['value'] . chr(10);
    }

    public function getData()
    {
        $data = [
            'first' => ['value' => $this->first, 'color' => '#000000'],
            'keyword1' => ['value' => $this->name, 'color' => '#000000'],
            'keyword2' => ['value' => $this->mobile, 'color' => '#000000'],
            'keyword3' => ['value' => $this->number, 'color' => '#000000'],
            'keyword4' => ['value' => $this->time, 'color' => '#000000'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}