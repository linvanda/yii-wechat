<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * Date: 2015/7/23
 * Time: 12:03
 */

namespace app\framework\weixin\qy;

use app\framework\weixin\qy\interfaces\QyWeiXinAccessTokenInterface;
use app\framework\weixin\qy\interfaces\QyWeiXinInterface;
use app\framework\weixin\qy\models\msg\AbstractMessage;
use app\framework\weixin\qy\models\receive\ReceiveMessage;
use app\framework\weixin\qy\models\receive\ResponseMessage;
use app\framework\weixin\qy\models\user\User;

class QyWeiXin implements QyWeiXinInterface
{
    /**
     * 租户代码
     * @var string
     */
    private $_tenantCode;

    /**
     * 企业号(若填写租户代码后，该值可以忽略)
     * @var string
     */
    private $_corpId;
    /**
     * 应用Code
     * @var string
     */
    public $_appCode;

    /**
     * 企业微信实例
     * @var string
     */
    public $_qyAccessTokenInterface;

    /**
     * 企业微信消息接口
     * @param string $appCode
     * @param string $tenantCode
     * @param string $corpId
     * @param QyWeiXinAccessTokenInterface|null $accessTokenInterface
     */
    public function __construct($appCode = '', $tenantCode = '', $corpId = '', QyWeiXinAccessTokenInterface $accessTokenInterface = null)
    {
        $this->_tenantCode = $tenantCode;
        $this->_corpId = $corpId;
        $this->_appCode = empty($appCode) ? \Yii::$app->id : $appCode;
        $this->_qyAccessTokenInterface = (!isset($accessTokenInterface)) ? new QyWeiXinAccessToken($this->getTenantCode()) : $accessTokenInterface;
    }

    /**
     * 获取AccessToken
     * @param $opToken
     * @return bool|mixed
     */
    public function getAccessToken($opToken = '')
    {
        return $this->_qyAccessTokenInterface->getAccessToken($this->_appCode, $opToken);
    }

    /**
     * 获取企业微信JsApiSignPackage
     * @return mixed
     */
    public function getWxJsApiSignPackage()
    {
        $wxConfig = $this->_qyAccessTokenInterface->getConfig($this->_appCode);
        $jsApiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string1 = 'jsapi_ticket=' . $jsApiTicket . '&noncestr=' . $nonceStr . '&timestamp=' . $timestamp . '&url=' . $url;
        $signature = sha1($string1);

        $signPackage = [
            "appId" => empty($wxConfig['corp_id']) ? $this->_corpId : $wxConfig['corp_id'],
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string1
        ];
        return $signPackage;
    }

    /**
     * 生成NonceStr
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取企业微信JsApiTicket
     * @return mixed
     */
    public function getJsApiTicket()
    {
        $cacheKey = $this->getJsApiTicketCacheKey();
        //从缓存中获取JsApiTicket
        $jsApiTicket = \Yii::$app->cache->get($cacheKey);
        if (empty($jsApiTicket)) {
            //调用接口获取
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket';
            $res = $this->wxRequest('获取jsapi_ticket', $url, 'get');
            if ($res !== false && !empty($res['ticket'])) {
                $jsApiTicket = $res['ticket'];
                \Yii::$app->cache->set($cacheKey, $jsApiTicket, (intval($res['expires_in']) - 600));
            }
        }
        return $jsApiTicket;
    }

    /**
     * 获取企业微信JsApiTicket缓存key
     * @return string
     * @throws \Exception
     */
    private function getJsApiTicketCacheKey()
    {
        return $this->getTenantCode() . ':qyJsApiTicket' . ':' . $this->_appCode;
    }

    /**
     * 清除企业微信JsApiTicket缓存
     * @return bool
     */
    private function clearJsApiTicketCache()
    {
        return \Yii::$app->cache->delete($this->getJsApiTicketCacheKey());
    }

    /**
     * 获取企业微信媒体文件
     * @param $mediaId
     * @return mixed
     */
    public function getMedia($mediaId)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/media/get?media_id=' . $mediaId;
        $res = $this->wxRequest('获取媒体文件', $url, 'get', null, true, true);
        if ($res !== null && isset($res[1])) {
            $json = json_decode($res[1], true);
            if (isset($json['errcode'])) {
                return null;
            }
        }
        return $res;
    }

    /**
     * 新增临时素材
     * @param string $type 分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @param string $file 素材文件本地路径，如果是url路径请先自行保存到本地再调用本接口
     * @return object {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     */
    public function uploadMedia($type, $file, $mimeType = '')
    {
        $file = realpath($file);
        $allowTypes = ['image', 'voice', 'video', 'file'];
        if (!in_array($type, $allowTypes)) {
            throw new \InvalidArgumentException("不支持的素材上传类型{$type},允许的类型值" . implode('、', $allowTypes));
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException("文件不存在：{$file}");
        }

        $size = filesize($file);
        switch ($type) {
            case 'image':
                $maxSize = 2 * 1024 * 1024;
                break;
            case 'voice':
                $maxSize = 5 * 1024 * 1024;
                break;
            case 'video':
                $maxSize = 10 * 1024 * 1024;
                break;
            case 'file':
                $maxSize = 20 * 1024 * 1024;
                break;
            default :
                break;
        }

        if ($size > $maxSize) {
            throw new \Exception("上传素材文件大小超出限制,图片2M，音频5M，视频10M，文件20M");
        }
        // 不能直接调用基类的exceute方法
        $extend = pathinfo($file);
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = empty($mimeType) ? $finfo->file($file) : $mimeType;
        $data = ['name' => $extend["filename"], 'file' => new \CURLFile($file), 'type' => $mime];
        $ch = curl_init();
        // 设置最大10分钟要上传完成
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $access_token = $this->getAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * 发送消息
     * @param AbstractMessage $message
     * @return bool|mixed
     */
    public function sendMessage(AbstractMessage $message)
    {
        if (!isset($message)) {
            return false;
        }
        if (empty($message->agentid)) {
            $wxConfig = $this->_qyAccessTokenInterface->getConfig($this->_appCode);
            $message->agentid = $wxConfig['agent_id'];
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send';
        return $this->wxRequest('发送消息', $url, 'post', json_encode($message, JSON_UNESCAPED_UNICODE), false, false, '', true);
    }

    /**
     * 发送微信客服消息
     * @param string $senderId 当senderType为企业号时代表企业用户id，当senderType为openid时代表公众号用户的openid
     * @param string $kfId 客服人员的企业userid
     * @param string $msgType 消息类型，text:文本消息,file:文件,image:图片
     * @param string $senderType 客服消息发送者的类型，userid:代表是企业号用户，openid则代表公众号的用户
     * @param string $data 发送数据，如果消息类型是文本则代表消息内容，若是文件或图片，则代表文件(图片)资源的media_id
     * @return mixed
     */
    public function sendKFMessage($senderId, $kfId, $data, $msgType = 'text', $senderType = 'userid', $receiverType = 'kf')
    {
        $params = [
            'sender' => [
                'type' => $senderType,
                'id' => $senderId
            ],
            'receiver' => [
                'type' => $receiverType,
                'id' => $kfId
            ],
            'msgtype' => $msgType
        ];

        switch ($msgType) {
            case 'text':
                $params['text'] = [
                    'content' => $data
                ];
                break;
            default:
                $params[$msgType] = [
                    'media_id' => $data
                ];
                break;
        }

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/kf/send';
        return $this->wxRequest('发送消息', $url, 'post', json_encode($params, JSON_UNESCAPED_UNICODE), false);
    }

    /**
     * 创建企业微信用户
     * @param User $user
     * @param $isReturnBool
     * @return mixed
     */
    public function createUser(User $user, $isReturnBool)
    {
        if (isset($user) === false || empty($user->userid) || empty($user->name)) {
            return false;
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/create';
        return $this->wxRequest('创建用户', $url, 'post', json_encode($user, JSON_UNESCAPED_UNICODE), !$isReturnBool);
    }

    /**
     * 根据OAuth2.0返回的code获取企业号用户的id
     * @param $code
     * @return mixed
     */
    public function getUserId($code)
    {
        if (empty($code)) {
            return false;
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?code=' . $code;
        $res = $this->wxRequest('获取用户ID', $url, 'get');
        if ($res !== false && !empty($res['UserId'])) {
            return $res['UserId'];
        }
        return false;
    }

    /**
     * 获取微信用户信息
     * @param $userId
     * @return mixed
     */
    public function getUser($userId)
    {
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?&userid=' . $userId;
        return $this->wxRequest('获取用户', $url, 'get');
    }

    /**
     * 获取微信用户具体的属性
     * @param $userId
     * @param $attribute
     * @return mixed
     */
    public function getUserByAttribute($userId, $attribute)
    {
        if (empty($userId)) {
            return null;
        }
        $user = $this->getUser($userId);
        if (isset($user)) {
            if (empty($attribute)) {
                return $user;
            } else {
                return $user[$attribute];
            }
        }
        return null;
    }

    /**
     * 删除菜单
     * @param $agentId
     * @return mixed
     */
    public function deleteMenu($agentId)
    {
        if (empty($agentId)) {
            return false;
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/menu/delete?agentid=' . $agentId;
        return $this->wxRequest('删除菜单', $url, 'get', null, false);
    }

    /**
     * 创建菜单
     * @param $agentId
     * @param $buttons
     * @return mixed
     */
    public function createMenu($agentId, $buttons)
    {
        if (empty($agentId) || isset($buttons) === false) {
            return false;
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?agentid=' . $agentId;
        return $this->wxRequest('创建菜单', $url, 'post', json_encode($buttons, JSON_UNESCAPED_UNICODE), false);
    }

    /**
     * 接收消息
     * @return array|null|string
     */
    public function receiveMessage()
    {
        $wxConfig = $this->_qyAccessTokenInterface->getConfig($this->_appCode);
        if (isset($wxConfig) && $wxConfig !== false) {
            $receiver = new ReceiveMessage($this->_corpId, $wxConfig['token'], $wxConfig['encoding_aes_key']);
            return $receiver->getMessage();
        }
        return null;
    }

    /**
     * 成员登陆url
     * @param $params
     * @return string
     */
    public function getMemberLoginUrl($params)
    {
        $default = ['state' => time() + 30, 'usertype' => $params['userType'] ? $params['userType'] : 'admin'];
        $queryStr = http_build_query(array_merge($default, $params));
        return "https://qy.weixin.qq.com/cgi-bin/loginpage?{$queryStr}";
    }

    /**
     * 获取成员的登陆信息
     * @param $authCode
     * @param $opToken
     * @return bool|mixed
     */
    public function getMemberLoginInfo($authCode, $opToken = '')
    {
        if (empty($authCode)) {
            return false;
        }
        $url = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?';
        return $this->wxRequest('获取成员的登陆信息', $url, 'post', json_encode(['auth_code' => $authCode], JSON_UNESCAPED_UNICODE), true, false, $opToken);
    }

    /**
     * 获取登录企业号官网的url
     * @param $login_ticket
     * @param $agent_id
     * @param string $target
     * @param $opToken
     * @return bool|mixed
     */
    public function getQyWeixinSiteUrl($login_ticket, $agent_id, $target = 'agent_setting', $opToken = '')
    {
        if (empty($login_ticket) || empty($agent_id)) {
            return false;
        }

        $url = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_login_url?';
        $params = ['login_ticket' => $login_ticket, 'target' => $target, 'agentid' => $agent_id];
        return $this->wxRequest('获取登录企业号官网的url', $url, 'post', json_encode($params, JSON_UNESCAPED_UNICODE), true, false, $opToken);
    }

    /**
     * 获取被动响应消息
     * @param string|array|object $msg
     * @return null|string
     */
    public function getResponseMessage($msg)
    {
        $wxConfig = $this->_qyAccessTokenInterface->getConfig($this->_appCode);
        if (isset($wxConfig) && $wxConfig !== false) {
            $responseMessage = new ResponseMessage($this->_corpId, $wxConfig['token'], $wxConfig['encoding_aes_key'], $msg);
            return $responseMessage->getMessage();
        }
        return null;
    }

    /**
     * 清除相关的缓存：企业微信配置信息、AccessToken、JsAPI_Ticket、等等
     */
    public function clearCache()
    {
        $this->clearJsApiTicketCache();
        $this->_qyAccessTokenInterface->clearAccessTokenCache($this->_appCode);
        $this->_qyAccessTokenInterface->clearAccessTokenCache($this->_appCode . 'DefaultToken');
        $this->_qyAccessTokenInterface->clearAccessTokenCache($this->_appCode . 'ProviderToken');
        $this->_qyAccessTokenInterface->clearAccessTokenCache($this->_appCode . 'SuiteToken');
        $this->_qyAccessTokenInterface->clearConfigCache($this->_appCode);
    }

    /**
     * 获取租户代码
     * @return mixed
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getTenantCode()
    {
        if (!empty($this->_tenantCode)) {
            return $this->_tenantCode;
        }

        $tenantReader = \Yii::$container->get('app\framework\biz\tenant\TenantReaderInterface');
        if (!isset($tenantReader)) {
            throw new \Exception('未注入app\framework\biz\tenant\TenantReaderInterface实例');
        }

        $tenantCode = $tenantReader->getCurrentTenantCode();
        if (!empty($tenantCode)) {
            return $this->_tenantCode = $tenantCode;
        }

        //若有指定企业号，则可以通过企业号及AppCode获取租户Code
        if (!empty($this->_corpId)) {
            $configDb = \Yii::$app->getDb();//获取配置库Db
            $sql = 'SELECT a.tenant_code FROM wx_suite_auth AS a INNER JOIN wx_suite_auth_app AS b ON a.id=b.suite_auth_id AND b.is_deleted=0 INNER JOIN wx_suite_app AS c ON b.app_id=c.id AND c.is_deleted=0 AND c.app_code=:app_code WHERE a.corp_id=:corp_id AND a.is_deleted=0';
            $tenantCode = $configDb->createCommand($sql, [':app_code' => $this->_appCode, ':corp_id' => $this->_corpId])->queryScalar();
            return $this->_tenantCode = $tenantCode;
        }
        return false;
    }

    /**
     * 给URL添加access_token参数
     * @param $url
     * @param $opToken
     */
    private function urlAppendAccessToken(&$url, $opToken = '')
    {
        $accessToken = $this->getAccessToken($opToken);

        if (empty($accessToken)) {
            $url = '';
            return;
        }
        $url .= (strstr($url, '?') === false) ? '?' : '&';
        $url .= ('access_token=' . $accessToken);
    }

    /**
     * 处理返回结果
     * @param $res
     * @param $url
     * @param $remark
     * @param bool $returnResponse
     * @param bool $returnErrcode
     * @return bool|mixed
     */
    private function processRequestResult($res, $url, $remark, $returnResponse = true, $returnErrcode = false)
    {
        if (isset($res)) {
            $res = json_decode($res, true);
            if (isset($res['errcode']) && intval($res['errcode']) > 0) {
                \Yii::info($remark . '微信接口(' . $url . ')请求错误,错误码:' . $res['errcode']);
            }
            if ($returnResponse) {
                return $res;
            }
            if ($returnErrcode) {
                return intval($res['errcode']);
            }
            if (intval($res['errcode']) > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            \Yii::error($remark . '微信接口(' . $url . ')请求错误,未响应');
            return false;
        }
    }

    /**
     * 微信请求
     * @param $remark
     * @param $url
     * @param $method
     * @param null $data
     * @param bool|true $returnResponse
     * @param bool|false $isMedia 是否是请求媒体文件,默认为否
     * @param string $opToken 令牌类型
     * @param bool|false $returnErrcode 是否返回错误码
     * @return array|bool|mixed
     */
    private function wxRequest($remark, $url, $method, $data = null, $returnResponse = true, $isMedia = false, $opToken = '', $returnErrcode = false)
    {
        $this->urlAppendAccessToken($url, $opToken);
        if (empty($url)) {
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        switch (strtolower($method)) {
            case 'post':
                curl_setopt($curl, CURLOPT_POST, 1);
                if (isset($data)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                break;
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        $httpInfo = $isMedia ? curl_getinfo($curl) : '';
        curl_close($curl);
        if ($isMedia) {
            return [$httpInfo, $res];
        }
        return $this->processRequestResult($res, $url, $remark, $returnResponse, $returnErrcode);
    }

    /**
     * 根据文件类型获取文件名扩展
     * @param $contentType
     * @return string
     */
    public function getFileExtByContentType($contentType)
    {
        switch ($contentType) {
            case 'application/x-png':
            case'image/png':
                return '.png';
            case'image/jpeg':
            case'image':
            case'application/x-jpg':
                return '.jpg';
            case'application/x-bmp':
                return '.bmp';
            default:
                return '.*';
        }
    }
}
