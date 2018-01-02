<?php

namespace app\framework\weixin\proxy\fw;

use app\framework\weixin\proxy\ApiBase;
use app\framework\weixin\interfaces\IAccessTokenHelper;

/**
 * 生成带参数的二维码
 */
class QrCode extends ApiBase
{

    public function __construct(IAccessTokenHelper $accessTokenHelper)
    {
        parent::__construct($accessTokenHelper);
    }

    /**
     * 生成带参数的二维码
     * @param $expireSeconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为30秒。
     * @param $actionName 二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
     * @param $sceneId 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     *                  场景值ID（字符串形式的ID），字符串类型，长度限制为1到64，仅永久二维码支持此字段  
     * @return object {
      "ticket": "gQH47joAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL2taZ2Z3TVRtNzJXV1Brb3ZhYmJJAAIEZ23sUwMEmm3sUw==",
      "expire_seconds": 60,
      "url": "http://weixin.qq.com/q\/kZgfwMTm72WWPkovabbI"
      }
     * @throws \ErrorException
     */
    public function create($expireSeconds, $actionName, $sceneId)
    {
        $actionName = trim($actionName);
        switch ($actionName) {
            case 'QR_SCENE':
                $data = [
                    'expire_seconds' => $expireSeconds,
                    'action_name' => $actionName,
                    'action_info' => [
                        'scene' => [
                            'scene_id' => (int) $sceneId
                        ]
                    ]
                ];
                break;
            case 'QR_LIMIT_SCENE':
                $data = [
                    'action_name' => $actionName,
                    'action_info' => [
                        'scene' => [
                            'scene_id' => (int) $sceneId
                        ]
                    ]
                ];
                break;
            case 'QR_LIMIT_STR_SCENE':
                $data = [
                    'action_name' => $actionName,
                    'action_info' => [
                        'scene' => [
                            'scene_str' => $sceneId
                        ]
                    ]
                ];
                break;
        }
        $result = $this->execute("https://api.weixin.qq.com/cgi-bin/qrcode/create", "POST", "生成带参数的二维码", $data);
        return $result;
    }
}
