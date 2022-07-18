<?php

use Illuminate\Database\Capsule\Manager as DB;
use Wxxiong6\WxxLogger\WxxLogger as Logger;
use Service\App;
use Yaf\Controller_Abstract;

class BaseController extends Controller_Abstract
{

    public $header;
    /**
     * @var Redis
     */
    public $redis;
    public $config;

    public function init()
    {
        ob_start();
        $params = $this->getRequest()->getParams();
        $signCheck = \Yaf\Application::app()->getConfig()->get('sign')->get('check')->get('status');

        if ($signCheck){
            $secret = \Yaf\Application::app()->getConfig()->get('sign')->get('ali')->get('secret');
            $this->checkSign($params,$secret);
        }

        $isCheckToken = App::isCheckToken($this->getRequest());
//         $this->header = App::headCheck($isCheckToken);
//         $uid = $this->header['uid'] ?? 0;
//         $logConfig['prefix'] = function () use ($uid) {
//             $phone = UserService::getInstance()->getUserInfoCache($uid, 'cellphone');
//             $phone = $phone ?? '-';
//             $ip = $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
//             return "[{$ip}][{$phone}]";
//         };
//        Logger::getInstance()->setConfig($logConfig);
        /** @var redis $redis */
        $redis = \Yaf\Registry::get('redis');
        $this->redis = $redis;
    }

    /**
     *  接口回调函数
     * @param array $data 数据结构
     * @param string $code 错误码或者是整个数据结构
     * @param string $msg 提示信息
     * @return void
     */
    final public function callback($data = [], $code = '0000', $msg = '')
    {
        $decrypt = \Yaf\Application::app()->getConfig()->get('security')->get('data')->get('encrypt');
        if ($decrypt) {
            $security = 1;
        } else {
            $security = 0;
        }
        try {
            App::callback($code, $msg, $data, $security);
        } catch (Exception $exception) {
            Logger::error("exception:" . $exception->getMessage());
        }
    }

    public function __destruct()
    {
        $content = ob_get_contents();
        ob_end_flush();
        Logger::info($content, '接口响应:' . $this->getRequest()->getRequestUri());
        $events = DB::getQueryLog();
        if (!empty($events)) {
            $log = "\n    ";
            foreach ($events as $event) {
                $sql = str_replace("?", "'%s'", $event['query']);
                $log .= vsprintf($sql, $event['bindings']) . "\n    ";
            }
            Logger::info(rtrim($log, "\n    "), 'SQL');
        }

        Logger::getInstance()->flush();
    }


    /**
     * 生成签名
     * @param array $params
     * @param string $secret 密钥
     * @return string
     */
    public function createSign(array $params, string $secret): string
    {
        ksort($params);
        $str = '';
        foreach ($params as $k => $v) {
            $str .= $k . $v;
        }
        return md5(sha1($str) . $secret);
    }


    /**
     * 验签
     * @param $params
     * @param $secret
     * @throws Exception
     */
    public function checkSign($params, $secret)
    {
        if (empty($params['sign'])) {
            $this->callback([],0000,'签名不能为空');
        }
        $oldSign = $params['sign'];
        unset($params['sign']);

        $sign = $this->createSign($params, $secret);

        if ($oldSign !== $sign) {
            $this->callback([],0000,'签名错误');
        }
    }

}