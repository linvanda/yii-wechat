<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 11:24
 */

namespace app\framework\weixin\qy\interfaces;

use app\framework\weixin\qy\models\msg\AbstractMessage;
use app\framework\weixin\qy\models\user\User;

interface QyWeiXinInterface
{
    /**
     * 获取AccessToken
     * @return mixed
     */
    public function getAccessToken();

    /**
     * 获取企业微信JsApiSignPackage
     * @return mixed
     */
    public function getWxJsApiSignPackage();

    /**
     * 获取企业微信JsApiTicket
     * @return mixed
     */
    public function getJsApiTicket();

    /**
     * 获取企业微信媒体文件
     * @param $mediaId
     * @return mixed
     */
    public function getMedia($mediaId);

    /**
     * 发送消息
     * @param AbstractMessage $message
     * @return mixed
     */
    public function sendMessage(AbstractMessage $message);

    /**
     * 发送微信客服消息
     * @param string $senderId 当senderType为企业号时代表企业用户id，当senderType为openid时代表公众号用户的openid
     * @param string $kfId 客服人员的企业userid
     * @param string $msgType 消息类型，text:文本消息,file:文件,image:图片
     * @param string $senderType 客服消息发送者的类型，userid:代表是企业号用户，openid则代表公众号的用户
     * @param string $data 发送数据，如果消息类型是文本则代表消息内容，若是文件或图片，则代表文件(图片)资源的media_id
     */
    public function sendKFMessage($senderId, $kfId, $data, $msgType = 'text', $senderType = 'userid');

    /**
     * 创建企业微信用户
     * @param User $user
     * @param $isReturnBool
     * @return mixed
     */
    public function createUser(User $user, $isReturnBool);


    /**
     * 根据OAuth2.0返回的code获取企业号用户的id
     * @param $code
     * @return mixed
     */
    public function getUserId($code);

    /**
     * 获取微信用户信息
     * @param $userId
     * @return mixed
     */
    public function getUser($userId);

    /**
     * 获取微信用户具体的属性
     * @param $userId
     * @param $attribute
     * @return mixed
     */
    public function getUserByAttribute($userId, $attribute);

    /**
     * 删除菜单
     * @param $agentId
     * @return mixed
     */
    public function deleteMenu($agentId);

    /**
     * 创建菜单
     * @param $agentId
     * @param $buttons
     * @return mixed
     */
    public function createMenu($agentId, $buttons);

    /**
     * 接收企业消息
     * @return mixed
     */
    public function receiveMessage();

    /**
     * 获取被动响应消息
     * @param string|array|object $msg
     * @return mixed
     */
    public function getResponseMessage($msg);

    /**
     * 清除相关的缓存
     * @return mixed
     */
    public function clearCache();

    /**
     * 获取租户代码
     * @return mixed
     */
    public function getTenantCode();
}
