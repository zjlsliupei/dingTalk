<?php


namespace liupei\dingtalk\extend;


use liupei\dingtalk\Client;

class File
{
    private $corpId = null;
    private $accessToken = null;
    private $agentId = null;

    public function __construct($corpId, $accessToken, $agentId)
    {
        $this->corpId = $corpId;
        $this->accessToken = $accessToken;
        $this->agentId = $agentId;
        if (empty($this->corpId)) {
            throw new \Exception('corpId不允许为空');
        }
        if (empty($this->accessToken)) {
            throw new \Exception('accessToken不允许为空');
        }
        if (empty($this->agentId)) {
            throw new \Exception('agentId不允许为空');
        }
    }

    /**
     * 获取空间
     * @param $domain
     * @return array|bool|mixed|null
     * @throws \ErrorException
     */
    public function getCustomSpace($domain)
    {
        $client = Client::newClient();
        $response = $client
            ->withAccessToken($this->accessToken)
            ->path('/cspace/get_custom_space')
            ->queryParam([
                'domain' => $domain,
                'agent_id' => $this->agentId
            ])
            ->request();
        if ($response->isSuccess()) {
            return strval($response->getData('spaceid'));
        }
        return false;
    }

    /**
     * 授权用户访问空间
     * @param string $userId
     * @param string $path 授权访问的路径，如授权访问所有文件传"/"，授权访问/doc文件夹传"/doc/"，需要utf-8 urlEncode, type=add时必须传递
     * @param string $type 权限类型，目前支持上传和下载，上传请传add，下载请传download
     * @param string $fields 授权访问的文件id列表，id之间用英文逗号隔开，如"fileId1,fileId2", type=download时必须传递
     * @param string $domain 企业内部调用时传入，授权访问该domain的自定义空间
     * @param int $duration 权限有效时间，有效范围为0~3600秒
     * @return bool
     * @throws \ErrorException
     */
    public function grantCustomSpace($userId, $path, $type, $fields,$domain, $duration = 3600)
    {
        $client = Client::newClient();
        $response = $client
            ->withAccessToken($this->accessToken)
            ->path('/cspace/grant_custom_space')
            ->queryParam([
                'domain' => $domain,
                'agent_id' => $this->agentId,
                'type' => $type,
                'userid' => $userId,
                'path' => $path,
                'fileids' => $fields,
                'duration' => $duration,
            ])
            ->request();
        return $response->isSuccess() ? true : false;
    }
}