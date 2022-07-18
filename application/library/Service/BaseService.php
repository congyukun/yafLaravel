<?php
/**
 * 实现单例
 * Class BaseService
 * @package Service
 */
namespace Service;

class BaseService
{

    /**
     * @var array
     */
    private static $_instance = [];

    private function __construct()
    {
    }

    private function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 获取实例
     * @return UserService | TokenService | NewsService | BrandService
     */
    public static function getInstance()
    {
        $className = get_called_class();
        if (empty(self::$_instance[$className])) {
            self::$_instance[$className] = new static();
        }
        return self::$_instance[$className];
    }
}