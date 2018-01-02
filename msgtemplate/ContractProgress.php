<?php
/**
 * Created by fanwq@mysoft.com.cn
 * Date: 2017/7/11
 * Time: 17:28
 * Description:签约进度通知
 */
namespace app\framework\weixin\msgtemplate;

class ContractProgress implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM411105692';

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
     * @var
     */
    public $first;

    /**
     * 项目房产
     * @var string
     */
    public $roomName;

    /**
     * @var 底部备注
     */
    public $remark;

    /**
     * 签约进度
     * @var string
     */
    public $progress;

    private $_first = ['value' => "", 'color' => '#000000'];
    private $_remark = ['value' => "", 'color' => '#000000'];

    public function __construct($first, $roomName, $progress, $remark, $url = '')
    {
        $this->_first['value'] = $first . chr(10);
        $this->roomName = $roomName;
        $this->progress = $progress;
        $this->_remark['value'] = chr(10) . $remark;
        $this->url = $url;
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->roomName, 'color' => '#000000'],
            'keyword2' => ['value' => $this->progress, 'color' => '#000000'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}
