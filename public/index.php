<?php

define('APP_PATH', dirname(__DIR__));
date_default_timezone_set("Asia/Shanghai");
$application = new Yaf\Application(APP_PATH . "/conf/application.ini");
$application->bootstrap()->run();
