<?php
/**
 * Created by kyYaf.
 * User: xiongweixing
 * Date: 2017/11/7
 * Time: 13:41
 */
use Yaf\Session;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Redis;

class TestController extends BaseController
{
    public function init()
    {
        parent::init();
    }
    public function indexAction()
    {
        \Service\UserService::getInstance()->show();
        \Service\UserService::getInstance()->show();
        \Service\UserService::getInstance()->show();
        \Service\UserService::getInstance()->show();
        \Service\UserService::getInstance()->show();
        //分页demo
        $page = 1;
        $perPage = 5;
        $arr = Capsule::table('test1')->select(['name','id'])->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        var_dump($arr->items());
        return false;
    }

    public function insertAction()
    {
        $find = TestModel::orderBy('created_at')->paginate(1, ['*'], 'page', 1)->items();


        Service\App::callback(200, '', $find);
        return false;
    }

    public function redisAction()
    {
        $redis = Redis::set("time", time());
        $v = Redis::get('time');
        echo "</pre>";
        print_r($v);
        echo "</pre>";
        die();
    }
}
