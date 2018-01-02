<?php

namespace app\framework\weixin\msgtemplate;

    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * 物业通知消息模板
 *
 * @author Lvq
 */
class PropertyNotice implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM204594069';
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
     * 主题
     * @var string
     */
    public $title;

    /**
     * 备注
     * @var string
     */
    public $remark;

    /**
     * 发布时间
     * @var string
     */
    public $publishedOn;


    private $_first = ['value' => '尊敬的业主:', 'color' => '#000000'];
    private $_remark = ['value' => '', 'color' => '#000000'];

    public function __construct($title, $publishedOn, $remark, $url)
    {
        $this->url = $url;
        $this->title = $title;
        $this->remark = $remark;
        $this->publishedOn = $publishedOn;
        $this->wrap();
    }

    private function wrap()
    {
        $this->_first['value'] = $this->_first['value'] . chr(10);
        $this->_remark['value'] = $this->remark;
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->title, 'color' => '#000000'],
            'keyword2' => ['value' => $this->publishedOn, 'color' => '#000000'],
            'keyword3' => $this->_remark
        ];

        return $data;
    }
}
