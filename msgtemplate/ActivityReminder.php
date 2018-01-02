<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 活动参与确认提醒消息模板
 *
 * @author Lvq
 */
class ActivityReminder implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM205360876';
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
     * 主题
     * @var string
     */
    public $subject;

    /**
     * 地点
     * @var string
     */
    public $address;


    /**
     * 活动时间
     * @var string
     */
    public $time;

    private $_remark = ['value' => '记得按时参加哦，不见不散！', 'color' => '#000000'];

    public function __construct($first, $subject, $address, $time, $url)
    {
        $this->url = $url;
        $this->first = $first;
        $this->subject = $subject;
        $this->address = $address;
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
            'keyword1' => ['value' => $this->subject, 'color' => '#000000'],
            'keyword2' => ['value' => $this->address, 'color' => '#000000'],
            'keyword3' => ['value' => $this->time, 'color' => '#000000'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}