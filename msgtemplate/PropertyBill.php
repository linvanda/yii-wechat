<?php

namespace app\framework\weixin\msgtemplate;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 通用账单模板
 *
 * @author Zengsy
 */
class PropertyBill implements IMsgTemplate
{
    const TEMPLATE_NO = 'TM00131';
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
     * 账单金额
     * @var string
     */
    public $pay;
    
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
    
    public function __construct($first,$userName ,$address, $pay  ,$remark, $url = '')
    {
        $this->first = $first;
        $this->address = $address;
        $this->pay = $pay;
        $this->userName = $userName;
        $this->remark = $remark;
        $this->url = $url;
    }
    
    public function getData()
    {
        $data = [
            'first' => ['value' => $this->first, 'color' => '#000000'],
            'userName' => ['value' => $this->userName, 'color' => '#000000'],
            'address' => ['value' => $this->address, 'color' => '#000000'],
            'pay' => ['value' => $this->pay, 'color' => '#0080ff'],
            'remark' => ['value' => $this->remark, 'color' => '#000000'],
        ];
        
        return $data;
    }
}