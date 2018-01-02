<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 11:39
 */

namespace app\framework\weixin\qy\models\user;

/**
 * 企业微信用户
 * Class User
 * @package app\framework\weixin\qy\models\user
 */
class User
{
    /**
     * 成员UserID
     * @var string
     */
    public $userid;

    /**
     * 成员名称
     * @var string
     */
    public $name;

    /**
     * 成员所属部门id列表 格式：[1, 2]
     * @var string
     */
    public $department;

    /**
     * 职位信息
     * @var string
     */
    public $position;

    /**
     * 手机号码。企业内必须唯一，mobile/weixinid/email三者不能同时为空
     * @var string
     */
    public $mobile;

    /**
     * 性别。1表示男性，2表示女性
     * @var string
     */
    public $gender;

    /**
     * 邮箱。企业内必须唯一
     * @var string
     */
    public $email;

    /**
     * 微信号。企业内必须唯一
     * @var string
     */
    public $weixinid;

    /**
     * 成员头像的mediaid，通过多媒体接口上传图片获得的mediaid
     * @var string
     */
    public $avatar_mediaid;

    /**
     * 扩展属性。扩展属性需要在WEB管理端创建后才生效，否则忽略未知属性的赋值
     * 格式:{"attrs":[{"name":"爱好","value":"旅游"},{"name":"卡号","value":"1234567234"}]}
     * @var string
     */
    public $extattr;

    /**
     * 构造器
     * @param $userid
     * @param $name
     */
    public function __construct($userid, $name)
    {
        $this->userid = $userid;
        $this->name = $name;
    }
}
