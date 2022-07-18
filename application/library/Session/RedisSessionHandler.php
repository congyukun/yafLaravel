<?php

namespace Session;

use SessionHandlerInterface;
use Yaf\Registry;
use Wxxiong6\WxxLogger\WxxLogger as Logger;

class RedisSessionHandler implements SessionHandlerInterface
{
    public $redis;
    public $keyPrefix;
    /**
     * @var int $maxlifetime 有效期
     */
    public $maxlifetime;
    public function __construct($keyPrefix)
    {
        $this->redis = Registry::get('redisSession');
        $this->keyPrefix = $keyPrefix;
        $this->maxlifetime = (int) ini_get('session.gc_maxlifetime');
    }


    public function close()
    {
        Logger::debug("session close", __METHOD__);
        return true;
    }

    public function destroy($sessionId)
    {
        $this->redis->del($this->keyPrefix .  $sessionId);
        Logger::debug("session destroy: session_id =".$this->keyPrefix .  $sessionId, __METHOD__);
        return true;
    }

    public function gc($lifetime)
    {
        Logger::debug("session  gc", __METHOD__);
        return true;
    }

    public function open($savePath, $sessionName)
    {
        Logger::debug("session open", __METHOD__);
        return true;
    }

    public function read($sessionId)
    {
        $sessionData = $this->redis->get($this->keyPrefix .  $sessionId);
        Logger::debug("session read: session_id =".$this->keyPrefix .  $sessionId);
        return $sessionData ?? '';
    }

    public function write($sessionId, $sessionData)
    {
        Logger::debug("session write: session_id =".$this->keyPrefix .  $sessionId . ",session_data=".$sessionData);
        return  (boolean) $this->redis->setex($this->keyPrefix .  $sessionId, $this->maxlifetime, $sessionData);
    }
}