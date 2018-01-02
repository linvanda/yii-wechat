<?php
namespace app\framework\weixin\msgtemplate;

/**
 * 手续办理进展消息模板
 * @author huanglc
 */
class ProceduresProgress implements IMsgTemplate{
	/**
	 * 模板编号
	 * @var string
	 */
	const TEMPLATE_NO = 'OPENTM406626319';
	
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
	public $first;
	
	/**
	 * 项目名称
	 * @var string
	 */
	public $projectName;
	
	/**
	 * 房间编号
	 * @var string
	 */
	public $buildingRoom;
	
	/**
	 * 办理时间
	 * @var string
	 */
	public $attendTime;
	
	/**
	 * 备注
	 * @var string
	 */
	public $remark;
	
	public function __construct($url, $first, $projectName, $buildingRoom, $attendTime, $remark) {
		$this->url = $url;
		$this->first = $first;
		$this->projectName = $projectName;
		$this->buildingRoom = $buildingRoom;
		$this->attendTime = $attendTime;
		$this->remark = $remark;
	}
	
	
	public function getData() {
		$data = [
			'first' => ['value' => $this->first, 'color' => '#000000'],
			'keyword1' => ['value' => $this->projectName, 'color' => '#459ae9'],
			'keyword2' => ['value' => $this->buildingRoom, 'color' => '#459ae9'],
			'keyword3' => ['value' => $this->attendTime, '#FF0000'],
			'remark' => ['value' => $this->remark, 'color' => '#000000']
		];
		
		return $data;
	}	
}
