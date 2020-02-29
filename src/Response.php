<?php


namespace liupei\dingtalk;


class Response
{
    /**
     * httt请求错误消息
     * @var string
     */
    private $httpErrMsg = '';

    /**
     * 钉钉返回的body数据
     * @var array|mixed
     */
    private $body = [];

    public function __construct($body)
    {
        $this->body = json_decode($body, true);
    }

    /**
     * 设置http错误信息
     * @param $httpErrMsg
     */
    public function setHttpErrMsg($httpErrMsg)
    {
        var_dump($httpErrMsg);
        $this->httpErrMsg = $httpErrMsg;
    }

    /**
     * 判断钉钉返回是否成功
     * @return bool
     */
    public function isSuccess()
    {
        if (isset($this->body['errcode']) and $this->body['errcode'] === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取钉钉返回错误消息
     * @return mixed|string
     */
    public function getErrMsg()
    {
        // 优先返回http错误
        if ($this->httpErrMsg) {
            return $this->httpErrMsg;
        }
        // 解析钉钉错误码
        if (isset($this->body['errmsg'])) {
            return $this->body['errmsg'];
        } else {
            return '';
        }
    }

    /**
     * 获取body信息
     * @param null $key 返回指定键名，如不传返回所有
     * @return array|mixed|null
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->body;
        } else {
            return isset($this->body[$key]) ? $this->body[$key] : null;
        }
    }
}