<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 预约看房消息模板
 *
 * @author Lvq
 */
class Appointment implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM402058297';
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
     * 业主
     * @var string
     */
    public $proprietor;

    /**
     * 房间
     * @var string
     */
    public $buildingRoom;

    /**
     * 预约时间
     * @var string
     */
    public $time;

    private $_remark = ['value' => '请您提前准备好办理交付所需的相关证件和费用，在预约时间内前往预约地点签到办理入伙手续。点击详情进入可【取消预约】', 'color' => '#000000'];

    public function __construct($first, $proprietor, $buildingRoom, $time, $url)
    {
        $this->url = $url;
        $this->first = $first;
        $this->proprietor = $proprietor;
        $this->buildingRoom = $buildingRoom;
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
            'first' => ['value' => '您好，您的预约信息如下：', 'color' => '#000000'],
            'keyword1' => ['value'=>$this->proprietor, 'color' => '#459ae9'],
            'keyword2' => ['value' => $this->buildingRoom, 'color' => '#459ae9'],
            'keyword3' => ['value' => $this->time, 'color' => '#FF0000'],
            'remark' => $this->_remark
        ];
        return $data;
    }
}