<?php


namespace liupei\dingtalk;

use Curl\Curl;

class Robot
{
    /**
     * 机器人基础路径
     * @var string
     */
    private $baseUrl = 'https://oapi.dingtalk.com/robot/send';

    /**
     * 最终请求的url
     * @var string
     */
    private $url = '';

    /**
     * Robot constructor.
     * @param string $accessToken 钉钉机器人access_token
     * @param string $secret 钉钉机器人的密码
     */
    public function __construct($accessToken, $secret = '')
    {
        $this->url = $this->createUrl($accessToken, $secret);
    }

    /**
     * 加签
     * @link 钉钉加签过程，https://ding-doc.dingtalk.com/doc#/serverapi2/qf2nxq
     * @param int $timestamp 签名时用的时间戳(毫秒)
     * @param string $secret 钉钉机器人的密码
     * @return string 签名字符串
     */
    private function sign($timestamp, $secret)
    {
        $raw = $timestamp . "\n" . $secret;
        $hmacStr = hash_hmac("sha256", $raw, $secret, true);
        $base64Str = base64_encode($hmacStr);
        return urlencode($base64Str);
    }

    /**
     * 生成url
     * @param string $accessToken 钉钉机器人access_token
     * @param string $secret 钉钉机器人的密码
     * @return string
     */
    private function createUrl($accessToken, $secret = '')
    {
        $url = $this->baseUrl . '?access_token=' . $accessToken;
        if ($secret) {
            $timestamp = $this->getMillisecond();
            $sign = $this->sign($timestamp, $secret);
            $url .= '&timestamp=' . $timestamp . '&sign=' . $sign;
        }
        return $url;
    }

    private function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 向群机器人发送消息
     * @param string $content 发送内容
     * @link https://ding-doc.dingtalk.com/doc#/serverapi2/qf2nxq
     * @return Response
     * @throws \ErrorException
     */
    public function send($content)
    {
        if (empty($content)) {
            $response = new Response('');
            $response->setHttpErrMsg('content内容不允许为空');
            return $response;
        }
        $content = is_string($content) ? $content : json_encode($content);
        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json; charset=utf-8');
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER , false);
        $curl->post($this->url, $content);
        $response = new Response($curl->response);
        if ($curl->error) {
            $response->setHttpErrMsg($curl->curl_error_message);
        }
        return $response;
    }
}