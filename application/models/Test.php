<?php

use Illuminate\Database\Capsule\Manager as Capsule;
class TestModel extends BaseModel
{
    //表名
    protected $table = 'test';
     //自动更新时间戳
    public $timestamps = true;
    //创建时间字段名称
    const CREATED_AT = 'created_at';
    //更新时间字段名称
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];


     public function __construct(array $attributes = [])
{


    parent::__construct($attributes);
}

	//验证数据demo
	public function add()
	{
		echo '<br/>';

		//验证数据
		$data = [
			'name' => '12332312321213'
		];
		//验证规则
		$rules = [
			'name' => 'required|string|min:2|max:5',
		];
		if (Rester\Validator::validators($rules,$data)) {
			echo 'ok';
		}else{
            echo Rester\Validator::getMessage();exit;
		}
	}

}