<?php
namespace Service;

use BrandModel;
class BrandService extends BaseService
{

    public function getOne()
    {
        return BrandModel::first();

    }
}
