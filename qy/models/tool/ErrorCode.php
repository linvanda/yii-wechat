<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * User: wangl10
 * Date: 2015/7/31
 * Time: 17:41
 */

namespace app\framework\weixin\qy\models\tool;

/**
 * Class ErrorCode
 * @package app\modules\wx\services\wx_tool
 */
class ErrorCode
{
    public static $OK = 0;
    public static $ValidateSignatureError = -40001;
    public static $ParseXmlError = -40002;
    public static $ComputeSignatureError = -40003;
    public static $IllegalAesKey = -40004;
    public static $ValidateCorpidError = -40005;
    public static $EncryptAESError = -40006;
    public static $DecryptAESError = -40007;
    public static $IllegalBuffer = -40008;
    public static $EncodeBase64Error = -40009;
    public static $DecodeBase64Error = -40010;
    public static $GenReturnXmlError = -40011;
}
