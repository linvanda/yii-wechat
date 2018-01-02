<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\framework\weixin\helper;

/**
 * 创建菜单按钮工厂类
 *
 * @author Chenxy
 */
class ButtonFactory
{
    /**
     * 创建菜单按钮
     * @param string $type 按钮类型，为menu则创建一个菜单项，包括name和子菜单
     * @return \app\framework\weixin\helper\PicSysphotoButton|\app\framework\weixin\helper\ViewButton|\app\framework\weixin\helper\MediaIdButton|\app\framework\weixin\helper\ScancodePushButton|\app\framework\weixin\helper\ViewLimitedButton|\app\framework\weixin\helper\ScancodeWaitmsgButton|\app\framework\weixin\helper\BaseButton|\app\framework\weixin\helper\PicPhotoOrAlbumButton|\app\framework\weixin\helper\LocationSelectButton|\app\framework\weixin\helper\ClickButton|\app\framework\weixin\helper\PicWeixinButton
     * @throws \yii\base\NotSupportedException
     */
    public static function create($type)
    {
        switch ($type) {
            case 'menu':
                return new BaseButton();
            case 'click':
                return new ClickButton();
            case 'view':
                return new ViewButton();
            case 'scancode_push':
                return new ScancodePushButton();
            case 'scancode_waitmsg':
                return new ScancodeWaitmsgButton();
            case 'pic_sysphoto':
                return new PicSysphotoButton();
            case 'pic_photo_or_album':
                return new PicPhotoOrAlbumButton();
            case 'pic_weixin':
                return new PicWeixinButton();
            case 'location_select':
                return new LocationSelectButton();
            case 'media_id':
                return new MediaIdButton();
            case 'view_limited':
                return new ViewLimitedButton();           
            default :
                throw new \yii\base\NotSupportedException("暂不支持{$type}类型的按钮");
        }
    }
}
