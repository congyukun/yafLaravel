<?php
namespace tools;

/**
 * 公用方法类
 *
 * User: congyukun
 * Date: 2021-12-08
 * Time: 下午1:41
 */


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Wxxiong6\WxxLogger\WxxLogger as Logger;

class hhzCommon
{
    /**
     * 二维数组按指定字段排序
     *
     * @param $list
     * @param $field
     * @param string $sortby
     * @return array|bool
     */
    public static function sortArrayByField($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $i => $data) {
                $refer[$i] = &$data[$field];
            }
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc': // 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
                default:
                    natcasesort($refer);
            }
            foreach ($refer as $key => $val) {
                $resultSet[] = &$list[$key];
            }
            return $resultSet;
        }
        return false;
    }

    /**
     * 初始化分页类返回值
     *
     * @return array
     */
    public static function getPaginateReturnData()
    {
        return [
            'total' => 0,
            'current_page' => 1,
            'per_page' => 10,
            'list' => [],
        ];
    }

    /**
     * 构造签名时间
     * $params 参数
     * $secret 秘钥
     * @return string
     */
    public static function createTimeSign($params, $secret = '')
    {
        $platform = $params['sign_platform'];
        $timestamp = $params['sign_timestamp'];
        $key = $secret;
        unset($params['sign_platform']);
        unset($params['sign_timestamp']);
        unset($params['sign']);
        $val = '';
        if (!empty($params)) {
            ksort($params);
            $val = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        }
        $sign = md5(md5(md5($platform . $key) . md5($timestamp)) . md5($val));
        return $sign;
    }


    /**
     * guzzle 发送请求
     * @param string $url
     * @param array $params
     * @param string $method
     * @param array $configs
     * @param string $contentType
     * @return array|string
     * @throws GuzzleException
     */
    public static function guzzleRequest(
        string $url,
        array $params = [],
        string $method = 'POST',
        array $configs = [],
        string $contentType = 'form_params'
    ) {
        $responseData = [];
        $configs['timeout'] = array_get($configs, 'timeout', 5);
        $client = new Client($configs);
        $params = strtoupper($method) === 'GET' ? ['query' => $params] : [$contentType => $params];
        try {
            $response = $client->request($method, $url, $params);
            $httpCode = $response->getStatusCode();
            $log = [
                'url' => $url,
                'params' => $params,
                'method' => $method,
                'configs' => $configs,
                'httpCode' => $httpCode,
                'response' => $response
            ];
            $logJson = json_encode($log);
            Logger::info("Guzzle请求日志：" . $logJson);
            if ($httpCode === 200) {
                $responseData = $response->getBody()->getContents();
            }
        } catch (RequestException $e) {
            $errorLog = [
                'url' => $url,
                'params' => $params,
                'method' => $method,
                'configs' => $configs,
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage()
            ];
            Logger::error("Guzzle请求错误日志：" . json_encode($errorLog));

        }
        return $responseData;
    }


    /**
     * 生成签名
     * @param array $data 请求的数据
     * @param string $secret 密钥
     * @return string
     */
    public static function createSign($data, $secret = '') {
        unset($data['sign'], $data['debug']);
        ksort($data);
        $str = '';
        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $str .= "&$key=$value";
        }
        $str = trim($str, "&") . $secret;
        return strtolower(md5($str));
    }






}
