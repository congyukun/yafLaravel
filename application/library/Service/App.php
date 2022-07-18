<?php
namespace Service;

use Security\Security;
use Security\Xxtea;
use Yaf\Registry;
use Yaf\Application;
use Yaf\Request\Http as RequestHttp;
use Wxxiong6\WxxLogger\WxxLogger as Logger;

/**
 * Class App
 * @package Service
 */
class App
{


    /**
     * 需要校验的HEADER信息
     * 按值的类型来做后面的校验级别 1做非空校验
     *
     * @var array
     */
    public static $checkHeader  = [
        'app_version'    => 1,
        'app_channel'    => 1,
        'device_id'      => 1,
        'device_type'    => 1,
        'device_version' => 1,
        'timestamp'      => 1,
        'token'          => 0,
        'signature'      => 1,
    ];

    /**
     * 用户提交的header
     * @var array
     */
    public static $headerInfo = [];

    /**
     * 校验出来的HEADER 结果信息
     * @var array
     */
    public static $header  = [
        'app_version'    => '',
        'app_channel'    => '',
        'device_id'      => '',
        'device_type'    => '',
        'device_version' => '',
        'timestamp'      => '',
        'token'          => '',
        'signature'      => '',
    ];


    /**
     * 系统错误信息
     * @var array
     */
    public static $errorCode = [
        -1  =>  '缺少请求参数' ,
        -2  =>  '非法操作,鉴权失败',
        -3  =>  'Token无效',
        -4  =>  '',
    ];

    /**
     * 系统错误信息字典
     * @var array
     */
    public static $errDict   = [
        'app_version'    => ['app_version',    MISS_REQUEST_PARAMS],
        'app_channel'    => ['app_channel',    MISS_REQUEST_PARAMS],
        'device_id'      => ['device_id',      MISS_REQUEST_PARAMS],
        'device_type'    => ['device_type',    MISS_REQUEST_PARAMS],
        'device_version' => ['device_version', MISS_REQUEST_PARAMS],
        'timestamp'      => ['timestamp',      MISS_REQUEST_PARAMS],
        'token'          => ['token',          TOKEN_CHECK_FAIL],
        'signature'      => ['signature',      MISS_REQUEST_PARAMS],
    ];

    /**
     * 获取Header头信息
     * @return array
     */
    public static function getHeader()
    {
        // 处理获取到的HEADER的信息
        if (isset($_SERVER) && !empty($_SERVER)) {
            foreach ($_SERVER as $k => $v) {
                if (stripos($k, 'HTTP_') === 0) {
                    $k = substr($k, 5);
                    $k = strtolower($k);
                    if (isset(self::$header[$k])) {
                        self::$header[$k]  = $v;
                    }
                    self::$headerInfo[$k]  = $v;
                }
            }
            return self::$header;
        }
    }

    /**
     * 获取Post加密参数
     * @param RequestHttp $request
     * @return array
     * @throws \Exception
     */
    public static function getRequest(RequestHttp $request)
    {
        if ($request->isPost()) {
            $decrypt = Application::app()->getConfig()->get('security')->get('data')->get('encrypt');
            $data = $request->getPost('data');
            Logger::info("原始参数:" . $data);
            if (empty($data)) {
                return [];
            }
            if ($decrypt) {
                $data = self::security()->decode($data);
                Logger::info("解密后的参数：" . $data);
            }

            parse_str($data, $post);
            return $post;
        }
        return [];
    }

    /**
     * 是否需要较验
     * @param $request
     * @param array $allowTokenApi
     * @return bool
     */
    public static function isCheckToken($request, $allowTokenApi = [])
    {
        $route = strtolower($request->controller .'/'. $request->action);
        if (empty($allowTokenApi)) {
            $allowTokenApi = [
                'index/getconfig'  =>'',
                'user/login'      => '',
                'user/sendsms'    => '',
                'register/checkid'=>'',
                'register/create' => '',
                'register/checkphone' =>'',
                'user/resetpasswd'    => '',
                'user/checkToken'     => '',
                'news/detail'     => '',
            ];
        }
        return (bool) !isset($allowTokenApi[$route]);
    }

    /**
     * 较验Header信息
     * @param bool $isCheckToken
     * @return array|json 如果校验成功返回用户,否则直接错误输出
     * @throws \Exception
     */
    public static function headCheck($isCheckToken = true)
    {
        $__header = self::getHeader();
        logger::info("header:".str_replace(["\r","\n"], '', print_r($__header, true)));
        //  如果获取不到HEADER 信息直接报错
        if (count($__header) < 1) {
            self::callback(['code' => -1, 'msg' => self::$errorCode[-1]]);
        }

        foreach (self::$checkHeader as $k => $v) {
            if (empty($__header[$k]) && $v == 1) {
                self::appCallback($k);
            }
        }

        //验Signature
        if (self::$checkHeader['signature'] == 1 && !self::checkSignature()) {
             self::appCallback('signature', -3);
        }

        //验Token
        if ($isCheckToken) {
            if (empty($__header['token'])) {
                 self::appCallback('token', -2);
            }
            $uid = self::checkToken();
            if (empty($uid)) {
                 self::appCallback('token', -3);
            }
            self::$header['uid'] = $uid;
        }

        return self::$header;
    }

    /**
     * 验证 Signature
     * Signature: sha1(strtolower(接口名称+timestamp+appkey+method+deviceId))
     * @return bool
     */
    public static function checkSignature()
    {
        $signature = self::createSignature();
        if ($signature === false) {
            return false;
        }
        if ($signature !== self::$header['signature']) {
            logger::info("验签失败：'{$signature}'， '" . self::$header['signature'] . "'");
            return false;
        }
        return true;
    }

    /**
     * 生成签名
     * sha1(strtolower(接口名称+timestamp+appkey+method+deviceId))
     * @return bool|string
     */
    public static function createSignature()
    {
        $config  = Registry::get('config');
        $apiName = Application::app()->getDispatcher()->getRequest()->controller . '/'.
            Application::app()->getDispatcher()->getRequest()->action;

        $method    = Application::app()->getDispatcher()->getRequest()->method;
        $timestamp = self::$header['timestamp'];
        $deviceId  = self::$header['device_id'];
        if (!isset($config['app_key'])) {
            logger::error('config app_key 未定义');
            return false;
        }
        $signature = strtolower($apiName . $timestamp . $config['app_key'] . $method . $deviceId);
        logger::info($signature);
        $signature = sha1($signature);
        return $signature;
    }

    /**
     * 系统提示
     * @param $errorCode
     * @param int $code
     * @throws \Exception
     * @return void
     */
    public static function appCallback($errorCode, $code = -1)
    {
        $callback = array(
            'code' => $code ,
            'msg'  => '非法操作'
        );
        if (isset(self::$errDict[$errorCode])) {
            $callback['code'] = self::$errDict[$errorCode][1] ;
            $text = array(
                -1 => '缺少'.self::$errDict[$errorCode][0].'参数' ,
                -2 => self::$errDict[$errorCode][0].'参数为空' ,
                -3 => self::$errDict[$errorCode][0].'校验失败',
            );
            $callback['msg']  = $text[$code];
        }
         self::callback($callback);
         return;
    }


    /**
     * 检验Token
     * @return mixed
     */
    public static function checkToken()
    {
        $token = self::$header['token'];
        return TokenService::getInstance()->checkToken($token);
    }

    /**
     * 验证UserId
     * @param $userId
     * @return bool
     */
    public static function checkId($userId)
    {
        // TODO
        return true;
    }

    /**
     * 删除Token
     * @param $token
     * @return mixed
     */
    public static function deleteToken($token)
    {
        return TokenService::getInstance()->deleteToken($token);
    }

    /**
     * 生成客户端的TOKEN
     *
     * 将签名过期时间指定为自 Epoch (1970 年 1 月 1 日 00:00:00 UTC) 以来的秒数。
     * 将拒绝在此时间 (根据服务器) 之后收到的请求。
     * @param int $uid 用户id
     * @return bool
     */
    public static function createToken($uid)
    {
        return TokenService::getInstance()->createToken($uid);
    }

    /**
     * 返回接口函数
     * @param int|array $code 状态码
     * @param string $msg 提示信息
     * @param array $data 返回数据
     * @param int $security 数据是否加密，0否 1 是
     * @throws \Exception
     * @return null
     */
    public static function callback($code, $msg = '', $data = [], $security = 0)
    {
        if (!is_array($code)) {
            $code = [
                'code' => $code,
                'msg'  => $msg,
                'data' => (empty($data) ? (object)$data : $data)
            ];
        }

        if (!isset($code['data'])) {
            $code['data'] = (empty($data) ? (object)$data : $data);
        }

        if (!isset($code['security'])) {
            $code['security'] =  $security;
        }

        if ($code['security']) {
            $dataString = PHP_VERSION > '5.4'
                ? json_encode($code['data'], JSON_UNESCAPED_UNICODE)
                : json_encode($code['data']);
            Logger::debug("未加密的数据：". $dataString);
            $code['data'] = self::security()->encode($dataString);
        }

        $json = PHP_VERSION > '5.4' ? json_encode($code, JSON_UNESCAPED_UNICODE) : json_encode($code);
        header("Content-Type:application/json; charset=utf-8");
        die($json);
    }


    /**
     * 调用加密方式
     * @param string $mode 加密方式
     * @return Xxtea | DES
     * @throws \Exception
     */
    public static function security($mode = 'data')
    {
        $config  = Registry::get('config');
        if (!isset($config['security'][$mode])) {
            throw new \Exception('config security 未定义');
        }
        $securityConfig = $config['security'][$mode];
        return Security::factory($securityConfig['mode'], $securityConfig['key']);
    }
}