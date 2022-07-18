<?php
/**
 * @name Bootstrap
 * @author desktop-9bo3mgh\xiongweixing
 * @desc 所有在Bootstrap类中,fdsakfjdksafdsk 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf\Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Redis;
use Wxxiong6\WxxLogger\WxxLogger as Logger;
use Yaf\Application;
use Yaf\Bootstrap_Abstract;
use Yaf\Registry;

class Bootstrap extends Bootstrap_Abstract
{

    public function _initConfig(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->autoRender(true);
        $dispatcher->disableView();
        $dispatcher->returnResponse(true);
        $arrConfig = Application::app()->getConfig()->toArray();
        Registry::set('config', $arrConfig);
    }

    public function _initLoader()
    {
        Yaf\Loader::import(APP_PATH.'/application/library/vendor/autoload.php');
        Yaf\Loader::import(Application::app()->getConfig()->application->directory.'/helper/functions.php');
        Yaf\Loader::import(Application::app()->getConfig()->application->directory.'/common/define.php');
    }

    public function _initLogger()
    {
        $logConfig = Registry::get('config')['logs'];
        if (isset($logConfig['logFile'])) {
            $logConfig['logFile'] .= date("md").'.log';
        }
        Logger::getInstance()->setConfig($logConfig);
    }

    public function _initErrorHandler(Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->setErrorHandler("myErrorHandler");
    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher)
    {
        //注册一个插件
//        $objSamplePlugin = new SamplePlugin();
//        $dispatcher->registerPlugin($objSamplePlugin);
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher)
    {
    }

    public function _initView(Yaf\Dispatcher $dispatcher)
    {
        //在这里注册自己的view控制器，例如smarty,firekylin
    }

    public function _initDBAdapter()
    {
        $capsule = new Capsule;
        $dbConfig = Registry::get('config')['database'];
        // 创建默认链接
        $capsule->addConnection($dbConfig);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        // 设置全局静态可访问
        $capsule->setAsGlobal();
        // 启动Eloquent
        $capsule->bootEloquent();
        //开启SQL log
        Capsule::enableQueryLog();
    }

    public function _initRedis()
    {
        $app = new Container;
        $app->singleton('redis', function () use ($app) {
            $redisConfig = Registry::get('config')['redis'];

            return new RedisManager($app, $redisConfig['driver'], $redisConfig);
        });

        $app->bind('redis.connection', function ($app) {
            return $app['redis']->connection();
        });
        Redis::setFacadeApplication($app);
        $redis = $app->get('redis');
        Registry::set('redis', $redis);
    }
    /*
     *  捕获程序异常fatal
     */
    public function _initCatchFatal()
    {
        ini_set("display_errors", "Off");
        register_shutdown_function("fatalHandler");
        set_error_handler("errorHandler");
    }
}

/**
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @return bool
 * @throws \Yaf\Exception
 */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    Logger::error("Unknown error type: [$errno] $errstr $errfile in line($errline)");
    throw new Yaf\Exception("Unknown error type: [$errno] $errstr $errfile in line($errline)");

    return true;
}

/*
 * 获取 fatal error
 */
function fatalHandler()
{
    $error = error_get_last();
    if ($error && ($error["type"] === ($error["type"] & E_FATAL))) {
        $errno = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr = $error["message"];
        errorHandler($errno, $errstr, $errfile, $errline);
    }
}

/*
 * 获取所有的 error
 */
function errorHandler($errno, $errstr, $errfile, $errline)
{
    $errstr = "Unknown error type: [$errno] $errstr $errfile in line($errline)";
    Logger::error($errstr);//记录日志
    $msg = ['code' => '500', 'msg' => 'fatal error~'];
    service\App::callback($msg);
}
