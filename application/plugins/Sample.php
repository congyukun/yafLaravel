<?php
/**
 * @name SamplePlugin
 * @desc Yaf定义了如下的6个Hook,插件之间的执行顺序是先进先Call
 * @see http://www.php.net/manual/en/class.yaf-plugin-abstract.php
 * @author desktop-9bo3mgh\xiongweixing
 */
use Service\App;
use Wxxiong6\WxxLogger\WxxLogger as Logger;

class SamplePlugin extends Yaf\Plugin_Abstract
{
    public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
        logger::info("请求接口：" . $request->getRequestUri());
        Yaf\Registry::set('response', $response);
        if ($request->isPost()) {
            $data = App::getRequest($request);
            if (!empty($data)) {
                foreach ($data as $k => $v) {
                    $request->setParam($k, $v);
                }
            }
        }
    }

    public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }


    public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function preResponse(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }

    public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response)
    {
    }
}
