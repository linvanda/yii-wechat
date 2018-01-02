<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 服务进度消息模板
 *
 * @author Lvq
 */
class ServiceProgress implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM207273073';
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
     * 服务类型
     * @var string
     */
    public $type;

    /**
     * 最新进度
     * @var string
     */
    public $progress;


    /**
     * 备注
     * @var string
     */
    public $remark;

    private $_first = ['value' => '', 'color' => '#000000'];
    private $_remark = ['value' => '', 'color' => '#000000'];

    public function __construct($first, $type, $progress, $remark, $url)
    {
        $this->url = $url;
        $this->first = $first;
        $this->type = $type;
        $this->progress = $progress;
        $this->remark = $remark;
        $this->wrap();
    }

    private function wrap()
    {
        $this->_first['value'] = $this->first . chr(10);
        $this->_remark['value'] = $this->remark ;
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->type, 'color' => '#000000'],
            'keyword2' => ['value' => $this->progress, 'color' => '#00FF7F'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}