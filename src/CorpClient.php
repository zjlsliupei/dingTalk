<?php


namespace liupei\dingtalk;

use think\facade\Cache;

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
        if (!empty($_accessToken)) {
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
}