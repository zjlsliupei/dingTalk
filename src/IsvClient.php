<?php


namespace liupei\dingtalk;

/**
 * 封装第三方企业应用接口
 * Class IsvClient
 * @package liupei\dingtalk
 */
class IsvClient extends Client
{
    public function withAccessToken($accessToken = true)
    {
        if ($accessToken !== true) {
            $this->queryParam(['access_token'=> $accessToken]);
        } else {
            $_accessToken = Cache::get('access_token');
            if (!empty($_accessToken)) {
                $this->queryParam(['access_token'=> $_accessToken]);
            } else {
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
                    $this->queryParam(['access_token'=> $response->getData('access_token')]);
                }
            }
        }

        return $this;
    }

    public function getSign()
    {
        
    }

    public function getTicket()
    {
        
    }

    public function getFile()
    {
        // TODO: Implement getFile() method.
    }
}