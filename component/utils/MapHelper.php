<?php
/**
 * Created by PhpStorm.
 * User: luw
 * Date: 2017/3/3
 * Time: 17:28
 */

namespace app\framework\weixin\component\utils;

use ReflectionClass;

class MapHelper
{
    /**
     * 获取Class的实例
     * @param string $className
     * @param array $params
     * @return object
     */
    public static function instance($className, $params = []) {
        $class = new ReflectionClass($className);
        return $class->newInstanceArgs($params);
    }

}