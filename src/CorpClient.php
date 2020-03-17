<?php


namespace liupei\dingtalk;


use liupei\dingtalk\extend\File;
use liupei\dingtalk\extend\Sign;

/**
 * 封闭企业内部应用接口
 * Class CorpClient
 * @package liupei\dingtalk
 */
class CorpClient extends Client
{
    /**
     * 携带access_token
     * @param bool $accessToken
     * @return $this
     * @throws \ErrorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function withAccessToken($accessToken = true)
    {
        if ($accessToken !== true) {
            $this->queryParam(['access_token'=> $accessToken]);
        } else {
            $this->queryParam(['access_token' => $this->getAccessToken()]);
        }
        return $this;
    }

    /**
     * 获取access_token
     * @return array|mixed|null
     * @throws \ErrorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getAccessToken()
    {
        $accessToken = Cache::get('access_token');
        if (!empty($accessToken)) {
            return $accessToken;
        }
        $client = Client::newClient();
        $response = $client->path('/gettoken')->queryParam([
            'appkey' => self::$config['app_key'],
            'appsecret' => self::$config['app_secret'],
        ])->request();
        if ($response->isSuccess()) {
            Cache::set('access_token',
                $response->getData('access_token'),
                $response->getData('expires_in') - 100
            );
        }
        return $response->getData('access_token');
    }

    /**
     * 获取js_ticket
     * @return array|mixed|null
     * @throws \ErrorException
     */
    public function getTicket()
    {
        $ticket = Cache::get('ticket');
        if (!empty($ticket)) {
            return $ticket;
        }
        $client = Client::newClient();
        $response = $client->path('/get_jsapi_ticket')->withAccessToken(true)->request();
        if ($response->isSuccess()) {
            Cache::set('ticket',
                $response->getData('ticket'),
                $response->getData('expires_in') - 100
            );
        }
        return $response->getData('ticket');
    }

    /**
     * 获取签名类
     * @return Sign|mixed
     * @throws \ErrorException
     */
    public function getSign()
    {
        $corpId = self::$config['corp_id'];
        return new Sign($corpId, $this->getAccessToken(), $this->getTicket(), self::$config['agent_id']);
    }

    /**
     * 获取文件类
     * @return File|mixed
     * @throws \ErrorException
     */
    public function getFile()
    {
        $corpId = self::$config['corp_id'];
        return new File($corpId, $this->getAccessToken(), self::$config['agent_id']);
    }
}