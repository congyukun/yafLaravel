<?php

use tools\hhzCommon;
use WikiBasicModel;

class IndexController extends BaseController
{

    public function IndexAction()
    {
        $data = WikiBasicModel::first();
        $this->callback($data);
    }
    public function testDBAction()
    {
        $data = WikiBasicModel::first();
        $this->callback($data);
    }
    public function testRedisAction()
    {
        $this->redis->set("time", time());
        $v = $this->redis->get('time');
        dd($v);
    }

    public function testApiAction()
    {
        $url = 'tapi.haohaozhu.com/todaynicegoods/beforelist';
        $params = [
            'month' => '2021-10',
            'page'=> 1
        ];
        $result = hhzCommon::guzzleRequest($url, $params);
        $this->callback($result);
    }
}
