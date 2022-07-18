<?php
/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author desktop-9bo3mgh\xiongweixing
 */
use Service\App;
use Yaf\Controller_Abstract;

class ErrorController extends Controller_Abstract
{

    //从2.1开始, errorAction支持直接通过参数获取异常
    public function errorAction($exception)
    {
        $this->getView()->assign("exception", $exception);
        switch ($exception->getCode()) {
            case YAF\ERR\NOTFOUND\MODULE:
            case YAF\ERR\NOTFOUND\CONTROLLER:
            case YAF\ERR\NOTFOUND\ACTION:
            case YAF\ERR\NOTFOUND\VIEW:
                $msg =   ['code' => -2, 'msg' => $exception->getMessage()];
                break;
            default:
                $msg = ['code' => -1, 'msg' => $exception->getMessage()];
                break;
        }
        App::callback($msg);
        return false;
    }
}
