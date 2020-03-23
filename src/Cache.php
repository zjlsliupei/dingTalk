<?php


namespace liupei\dingtalk;

/**
 * Class Cache目前只设计支持redis
 * @package liupei\dingtalk
 */
class Cache
{
    private static $config = [
        'host'   => '127.0.0.1',
        'port'   => 6379,
        'select' => 0,
        'password' => '',
        'expire' => 0,
        'prefix' => ''
    ];
    private static $handle = null;

    public static function init($option)
    {
        self::$handle = new \Redis();
        self::$config = array_merge(self::$config, $option);
        self::$handle->connect(self::$config["host"], self::$config["port"]);
        if (!empty(self::$config['password'])) {
            self::$handle->auth(self::$config['password']);
        }
        self::$handle->select(self::$config['select']);
    }

    public static function set($key, $value, $expireSecond = 0)
    {
        $_key = self::getKeyName($key);
        if (empty($expireSecond)) {
            return self::$handle->set($_key, $value);
        } else {
            return self::$handle->setex($_key,$expireSecond, $value);
        }
    }

    public static function get($key)
    {
        $_key = self::getKeyName($key);
        return self::$handle->get($_key);
    }

    /**
     * 获取key名称
     * @param string $key
     * @return string
     */
    private static function getKeyName($key)
    {
        if (isset(self::$config['prefix'])) {
            return self::$config['prefix'] . $key;
        }
        return $key;
    }
}