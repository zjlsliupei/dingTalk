<?php


namespace liupei\dingtalk\extend;


class Sign
{
    private $corpId = null;
    private $accessToken = null;
    private $ticket = null;
    private $agentId = null;

    public function __construct($corpId, $accessToken, $ticket, $agentId)
    {
        $this->corpId = $corpId;
        $this->accessToken = $accessToken;
        $this->ticket = $ticket;
        $this->agentId = $agentId;
        if (empty($this->corpId)) {
            throw new \Exception('corpId不允许为空');
        }
        if (empty($this->accessToken)) {
            throw new \Exception('accessToken不允许为空');
        }
        if (empty($this->ticket)) {
            throw new \Exception('ticket不允许为空');
        }
        if (empty($this->agentId)) {
            throw new \Exception('agentId不允许为空');
        }
    }

    /**
     * 获取企业签名
     * @param string $url
     * @return array
     */
    public function getCorpSign($url)
    {
        $nonceStr = uniqid();
        $timeStamp = time();
        $signature = $this->sign($this->ticket, $nonceStr, $timeStamp, $url);
        return $config = [
            'url'       => $url,
            'nonceStr'  => $nonceStr,
            'agentId'   => $this->agentId,
            'timeStamp' => $timeStamp,
            'corpId'    => $this->corpId,
            'signature' => $signature,
        ];
    }

    /**
     * 生成签名
     * @param string $ticket
     * @param string $nonceStr
     * @param int $timeStamp
     * @param string $url
     * @return string
     */
    private function sign($ticket, $nonceStr, $timeStamp, $url)
    {
        $plain = 'jsapi_ticket=' . $ticket .
            '&noncestr=' . $nonceStr .
            '&timestamp=' . $timeStamp .
            '&url=' . $url;
        return sha1($plain);
    }
}