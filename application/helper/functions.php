<?php
/**
 * Created by PhpStorm.
 * User: Tmac
 * Date: 2017/11/17
 * Time: 17:36
 */


if (!function_exists('p')) {
    function p($arr)
    {
        echo '<pre>', print_r($arr), '</pre>';
        die;
    }
}


/**
 * 成功返回json数据
 * @param int $code 状态0000为成功
 * @param string $msg 提示消息
 * @param array $data 数据
 * @return bool
 */
if (!function_exists('json_success')) {
    function json_success($data = array(), $code = '0000', $msg = 'success')
    {
        if ($code == '0000') {
            header('content-type:application/json;charset=utf8');
            $response = compact('code', 'msg', 'data');
            echo json_encode($response);
            return true;
        }
        return false;
    }
}


/**
 * 失败时候返回json数据
 * @param int $code 状态不为0000
 * @param string $msg
 * @param array $data
 * @return bool
 */
if (!function_exists('json_fail')) {
    function json_fail($data = array(), $code = '1000', $msg = 'fail')
    {
        if ($code != '0000') {
            header('content-type:application/json;charset=utf8');
            $response = compact('code', 'msg', 'data');
            echo json_encode($response);
        }
        return false;
    }
}
if (!function_exists('rzb')) {
    /*
     * 身份证认证
     * */
    function rzb($uname, $idnum)
    {
        $account = 'kyzc_admin'; //账号
        $key = 'pPLWyA9X'; //私钥
        $idNumber = $idnum;
        $name = urlencode($uname);
        $param = $idNumber . $account;
        $sign = strtoupper(md5(strtoupper(md5($param)) . $key));

        //简项认证
        $url = 'https://service.sfxxrz.com/simpleCheck.ashx?idNumber=' . $idNumber . '&name=' . $name .
            '&account=' . $account . '&pwd=' . $key . '&sign=' . $sign;

        //是否存在简项记录
        /*$url='https://service.sfxxrz.com/isSimpleCitizenExists.ashx?idNumber=' . $idNumber . '&name=' . $name .
        '&account=' . $account . '&pwd=' . $key . '&sign=' . $sign;*/

        //提取简项记录
        /*$url='https://service.sfxxrz.com/querySimpleCitizenData.ashx?idNumber=' . $idNumber . '&name=' . $name .
        '&account=' . $account . '&pwd=' . $key . '&sign=' . $sign;*/

        //查询余额
        /*$param=$account;
        $sign= strtoupper(md5(strtoupper(md5($param)) . $key));
        $url='https://service.sfxxrz.com/queryBalance.ashx?&account=' . $account . '&pwd=' . $key . '&sign=' . $sign;*/

        Wxxiong6\WxxLogger\WxxLogger::error('id check request: ' . $url);
        $html = file_get_contents($url);

        $result = json_decode($html, true);
        if ($result) {
            Wxxiong6\WxxLogger\WxxLogger::error('id check response: ' . json_encode($result));
        }

        return $result['Identifier']['Result'] == '一致' ? true : false;
    }
}

if (!function_exists('generatorSMSCode')) {
    /*
 * 随机数字
 * **/
    function generateSMSCode($length = 6)
    {
        $strings1 = array('3', '4', '5', '6', '7', '1', '2', '0', '8', '9');
        $strings2 = array('3', '4', '5', '6', '7', '1', '2', '0', '8', '9');
        $length2 = 3;
        $length1 = $length - $length2;

        $chrNum1 = $chrNum2 = "";
        $count1 = count($strings1);
        $count2 = count($strings2);
        for ($i = 1; $i <= $length1; $i++) {                             //循环随机取字符生成字符串
            $chrNum1 .= $strings1[rand(0, $count1 - 1)];
        }
        for ($i = 1; $i <= $length2; $i++) {                            //循环随机取字符生成字符串
            $chrNum2 .= $strings2[rand(0, $count2 - 1)];
        }
        return $chrNum1 . $chrNum2;
    }
}
if (!function_exists('is_serialized')) {
    function is_serialized($data)
    {
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }
        return false;
    }
}


if (!function_exists('getCommonConfig')) {
    /**
     * 获取公共配置数据,当参数 $key 为空，返回数据，否则返回相反相应的字符串值
     * @param string $key
     * @return array | string
     */
    function getCommonConfig($key = "")
    {
        if (!empty($key)) {
            $config = AppConfigModel::where(['key' => $key])->get(['val']);
            return $config[0]['val'];
        }

        $config = AppConfigModel::get(['id', 'key', 'val']);

        $result = [];
        if ($config) {
            foreach ($config as $value) {
                if (is_serialized($value['val'])) {
                    $result[$value['key']] = unserialize($value['val']);
                } else {
                    $result[$value['key']] = $value['val'];
                }
            }
        }
        return $result;
    }
}

/**
 * 获取当前最新版本信息
 * @param string $type 1：ios  2：android
 * @return array
 */
if (!function_exists('getAppVersion')) {
    function getAppVersion($type = 1)
    {
        $version = AppVersionModel::where(['type' => $type, 'state' => 1])->select([
            'id',
            'var',
            'version',
            'isforce',
            'down_path',
            'version_val'
        ])->orderBy(
            'id',
            'desc'
        )->first();
        return $version;
    }
}

if (!function_exists('getUploadDir')) {
    function getUploadDir()
    {
        $config = \Yaf\Registry::get('config')['upload'];
        $rootDirectory = $config['directory'];

        return $rootDirectory;
    }
}

if (!function_exists('getUploadHost')) {
    function getUploadHost()
    {
        $config = \Yaf\Registry::get('config')['upload'];
        $host = $config['host'];

        return $host;
    }
}

if (!function_exists('getProductRiskDescription')) {
    function getProductRiskDescription($risk)
    {
        $riskArr = RisklogController::pro_level;
        return isset($riskArr[$risk]) ? $riskArr[$risk] : $riskArr[0];
    }
}

if (!function_exists('getUserRiskDescription')) {
    function getUserRiskDescription($risk)
    {
        $riskArr = RisklogController::user_level;
        return isset($riskArr[$risk]) ? $riskArr[$risk] : $riskArr[0];
    }
}

if (!function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }

            $array = $array[$segment];
        }
        return $array;
    }
}
