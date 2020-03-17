<?php


namespace liupei\dingtalk;

use Curl\Curl;

abstract class Client
{
    /**
     * 钉钉oapi地址
     * @var string
     */
    protected $baseUrl = 'https://oapi.dingtalk.com';

    /**
     * 请求字符串参数
     * @var array
     */
    protected $queryParam = [];

    /**
     * 请求post参数
     * @var array
     */
    protected $postParam = [];

    /**
     * 请求方法,可选get post
     * @var string
     */
    protected $method = '';

    /**
     * 请求路径，如:/user/get
     * @var string
     */
    protected $path = '';

    /**
     * 配置信息
     * @var array
     */
    protected static $config = [];

    /**
     * 请求接口access_token
     * @link
     * @var null
     */
    protected $accessToken = null;

    /**
     * 事件列表
     * @var array
     */
    protected static $events = [];

    /**
     * 实例化client，根据config['type']实例不同client
     * @return CorpClient|IsvClient
     * @throws \Exception
     */
    public static function newClient()
    {
        if (isset(self::$config['type']) and self::$config['type'] == 'corp') {
            return new CorpClient();
        } else if (isset(self::$config['type']) and self::$config['type'] == 'corp') {
            return new IsvClient();
        } else {
            throw new \Exception('type参数不合法');
        }
    }

    /**
     * 设置全局参数
     * @param array $config
     */
    public static function config($config = [])
    {
        self::$config = $config;
        if (isset($config['cache'])) {
            // 设置缓存
            Cache::init($config['cache']);
        }
    }

    /**
     * 注册事件
     * @param string $eventName 事件名，支持before_request,after_request
     * @param function $callback 回调函数
     * @throws \Exception
     */
    public static function event($eventName, $callback)
    {
        if (!in_array($eventName, ['before_request','after_request'])) {
            throw new \Exception('eventName必须为before_request或after_request');
        }
        self::$events[$eventName] = $callback;
    }

    /**
     * @param true|string $accessToken 是否自动获取access_token,true:自动获取 ,否则使用传入的参数
     */
    public abstract function withAccessToken($accessToken = true);

    /**
     * 获取签名类
     * @return mixed
     */
    public abstract function getSign();

    /**
     * 获取ticket
     * @return mixed
     */
    public abstract function getTicket();

    /**
     * 获取文件类
     * @return mixed
     */
    public abstract function getFile();

    /**
     * 设置查询字符串，合并的方式
     * @param array $queryParam
     * @return $this
     */
    public function queryParam($queryParam = [])
    {
        $this->queryParam = array_merge($this->queryParam, $queryParam);
        return $this;
    }

    /**
     * 设置post参数，合并的方式
     * @param array $postParam
     * @return $this
     */
    public function postParam($postParam = [])
    {
        $this->postParam = array_merge($this->postParam, $postParam);
        return $this;
    }

    /**
     * 设置请求路径，如：/user/get
     * @param $path
     * @return $this
     */
    public function path($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 发送请求
     * @param true|string $method 请求类型,true:自动识别，否则使用传的参数，可选（post,get）
     * @return Response
     * @throws \ErrorException
     */
    public function request($method = true)
    {
        // 判断请求类型
        if ($method == true) {
            if (empty($this->postParam)) {
                $this->method = 'get';
            } else {
                $this->method = 'post';
            }
        } else {
            $this->method = $method;
        }
        // 组装参数
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $url = $this->baseUrl . $this->path;
        if ($this->method == 'post') {
            if (count($this->queryParam) > 0) {
                $url .=  '?' . http_build_query($this->queryParam);
            }
            $curl->setHeader('Content-Type', 'Application/json');
            // 触发执行前事件
            $this->trigger('before_request', [
                'url' => $url,
                'postParam' => $this->postParam,
                'method' => $this->method
            ]);
            $curl->post($url, $this->postParam);
        } else if ($this->method == 'get') {
            // 触发执行前事件
            $this->trigger('before_request', [
                'url' => count($this->queryParam) ? $url . '?' . http_build_query($this->queryParam) : $url,
                'postParam' => $this->postParam,
                'method' => $this->method
            ]);
            $curl->get($url, $this->queryParam);
        } else {
            throw new \Exception('不支持的request类型');
        }
        // 触发执行后事件
        $this->trigger('after_request', [
            'url' => $url,
            'postParam' => $this->postParam,
            'method' => $this->method,
            'http_error_message' => $curl->curl_error_message,
            'http_status' => $curl->getHttpStatus(),
            'body' => $curl->getResponse()
        ]);
        $response = new Response($curl->response);
        if ($curl->curl_error) {
            $response->setHttpErrMsg($curl->curl_error_message);
        }
        return $response;
    }

    /**
     * 触发事件
     * @param string $eventName
     * @param array $data
     */
    private function trigger(string $eventName, array $data)
    {
        if (isset(self::$events[$eventName]) and is_callable(self::$events[$eventName])) {
            $cb = self::$events[$eventName];
            $cb($data);
        }
    }
}