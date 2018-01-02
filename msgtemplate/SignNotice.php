<?php
namespace app\framework\weixin\msgtemplate;


/**
 * 签到成功通知模板
 * @author huanglc
 */
class SignNotice implements IMsgTemplate {
	/**
	 * 模板编号
	 * @var string
	 */
	const TEMPLATE_NO = 'OPENTM406626205';
	
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
	 * 顶部颜色
	 * @var string
	 */
	public $topColor = '#FF0000';
	
	/**
	 * 前言
	 * @var string
	 */
	public $first = '';
	
	/**
	 * 业主
	 * @var string
	 */
	public $proprietor;
	
	/**
	 * 房间编号
	 * @var string
	 */
	public $buildingRoom;
	
	/**
	 * 签到号码
	 * @var string
	 */
	public $signMumber;
	
	/**
	 * 签到时间
	 * @var string
	 */
	public $signTime;
	
	/**
	 * 备注
	 * @var string
	 */
	public $_remark = ['value' => '点击详情，可查看办理进度。', 'color' => '#000000'];
	
	public function __construct($first, $proprietor, $buildingRoom, $signMumber, $signTime, $url, $remark) {
		$this->first = $first;
		$this->proprietor = $proprietor;
		$this->buildingRoom = $buildingRoom;
		$this->signMumber = $signMumber;
		$this->signTime = $signTime;
		$this->url = $url;
		$this->wrap($remark);
	}
	
	private function wrap($remark){
		$this->_remark['value'] = $remark. $this->_remark['value'] . chr(10);
	}
	
	public function getData() {
		$data = [
			'first' => ['value' => $this->first, 'color' => '#000000'],
			'keyword1' => ['value' => $this->proprietor, 'color' => '#459ae9'],
			'keyword2' => ['value' => $this->buildingRoom, 'color' => '#459ae9'],
			'keyword3' => ['value' => $this->signMumber, 'color' => '#459ae9'],
			'keyword4' => ['value' => $this->signTime, 'color' => '#459ae9'],
			'remark' => $this->_remark
		];
		
		return $data;
	}
}