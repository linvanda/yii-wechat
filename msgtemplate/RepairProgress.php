<?php
/**
 * Created by fanwq@mysoft.com.cn
 * Date: 2016/7/8
 * Time: 17:28
 * Description:
 */
namespace app\framework\weixin\msgtemplate;

class RepairProgress implements IMsgTemplate
{
    const TEMPLATE_NO = 'OPENTM206026150';

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
     * 内容
     * @var string
     */
    public $contentNote;

    /**
     * @var 底部备注
     */
    public $remark;

    /**
     * @var
     */
    public $first;

    /**
     * 进度
     * @var string
     */
    public $progressNote;

    private $_first = ['value' => "", 'color' => '#000000'];
    private $_remark = ['value' => "", 'color' => '#000000'];

    public function __construct($first, $contentNote, $progressNote, $remark, $url = '')
    {
        $this->contentNote = $contentNote;
        $this->progressNote = $progressNote;
        $this->url = $url;
        $this->_first['value'] = $first . chr(10);
        $this->_remark['value'] = chr(10) . $remark;
    }

    public function getData()
    {
        $data = [
            'first' => $this->_first,
            'keyword1' => ['value' => $this->contentNote, 'color' => '#000000'],
            'keyword2' => ['value' => $this->progressNote, 'color' => '#3AC754'],
            'remark' => $this->_remark
        ];

        return $data;
    }
}
