<?php
/**
 * 坤元资产app的配置文件
 */


define("APP_TIME", time());
define("APP_DATE", date('Y-m-d H:i:s', APP_TIME));
//定义错误级别
define('E_FATAL', E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE);



//API code码
define("APP_REQUEST_SUCCESS", "0000");//请求成功
define("TOKEN_CHECK_FAIL", "1001");//token校验失败
define("MISS_REQUEST_PARAMS", "1002");//缺少header头参数
define("RISK_RANK_ALERT", "1003");//产品列表风险等级提示跳转
define("SMS_CODE_ERROR", "4001");//注册短信验证码错误


define("SCRIPT_ROOT", APP_PATH . "/application/library/Sms");//定义程序绝对路径 短信sdk用
define("DEFAULT_PAGE_SIZE", "10");//默认显示条数
define("HOME_PAGE_SHOW", "3");//首页产品列表展示条数
define("SMS_COUNT_LIMIT", 5);// 短信发送次数上限
define("ID5_CHECK_LIMIT", 5); // 短信发送次数上限


//API code码 用户登录
define("LOGIN_CELLPHONE_NULL", "1001");// 手机号不能为空
define("LOGIN_CELLPHONE_WRONG", "1002");//手机号码错误
define("LOGIN_PASSWORD_NULL", "1003");//密码不能为空
define("LOGIN_FAILED", "1004");// 手机号或密码错误
define("LOGIN_UNKNOWN_USERNAME", "1005");// 用户未注册
