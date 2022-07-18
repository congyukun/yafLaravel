<?php

use Service\BrandService;
class IndexController extends BaseController
{
    public function IndexAction()
    {
        $data = BrandService::getInstance()->getOne();
        $this->callback($data);
    }
}
