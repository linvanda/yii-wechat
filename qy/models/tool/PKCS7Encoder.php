<?php
/**
 * Created by wangl10@mysoft.com.cn.
 * User: wangl10
 * Date: 2015/7/31
 * Time: 18:26
 */
namespace app\framework\weixin\qy\models\tool;

class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     * @param  string $text 需要进行填充补位操作的明文
     * @return string 补齐明文字符串
     */
    public static function encode($text)
    {
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = static::$block_size - ($text_length % static::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = static::$block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param  string $text 解密后的明文
     * @return string 删除填充补位后的明文
     */
    public static function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > static::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}
