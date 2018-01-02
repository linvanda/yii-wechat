<?php
/**
 * Created by PhpStorm.
 * User: tianweilong
 * Date: 2017/3/7
 * Time: 12:02
 */

namespace app\framework\weixin\msgtemplate;


class PaymentNotice implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM203804479';

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
     * 账单地址
     * @var string
     */
    public $address;

    /**
     * @var string 缴费内容
     */
    public $content;

    /**
     * 缴费金额
     * @var string
     */
    public $amount;

    /**
     * 业主姓名
     * @var string
     */
    public $userName;

    /**
     * 备注
     * @var string
     */
    public $remark;

    public $first;



    public function __construct($first, $userName , $address, $content, $amount,$remark, $url = '')
    {
        $this->first = $first;
        $this->address = $address;
        $this->content = $content;
        $this->amount = $amount;
        $this->userName = $userName;
        $this->remark = $remark;
        $this->url = $url;
    }

    public function getData()
    {
        $data = [
            'first' => ['value' => $this->first, 'color' => '#000000'],
            'keyword1' => ['value' => $this->userName, 'color' => '#000000'],
            'keyword2' => ['value' => $this->address, 'color' => '#000000'],
            'keyword3' => ['value' => $this->content, 'color' => '#000000'],
            'keyword4' => ['value' => $this->amount, 'color' => '#0080ff'],
            'remark' => ['value' => $this->remark, 'color' => '#000000'],
        ];

        return $data;
    }
}