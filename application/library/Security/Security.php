<?php
namespace Security;

class Security
{
    /**
     * 静态工厂类
     * @param $className
     * @param $conf
     * @return Xxtea
     * @throws \Exception
     */
    public static function factory($className, $conf = '')
    {
        $className = __NAMESPACE__ . '\\'. $className;
        if (class_exists($className)) {
            return new $className($conf);
        }
        throw new \Exception($className.'不存在');
    }
}