<?php

namespace Goodwong\LaravelDingtalk\Services;

use Closure;
use Goodwong\LaravelDingtalk\Exception;
use Goodwong\LaravelDingtalk\InvalidAccessTokenException;
use Goodwong\LaravelDingtalk\SystemBusyException;
use GuzzleHttp\Client as GuzzleClient;

class Api
{
    /**
     * 通讯状态码
     */
     // 正常返回
    const RESULT_SUCCESS                = 0;
     // 系统繁忙，建议重试
    const RESULT_SYSTEM_BUSY            = -1;
     // 获取access_token时Secret错误，或者access_token无效
    const RESULT_INVALID_TOKEN          = 40001;
     // 不合法的access_token
    const RESULT_INVALID_ACCESS_TOKEN   = 40014;
     // access_token超时
    const RESULT_EXPIRES_ACCESS_TOKEN   = 42001;

    /**
     * 其它
     */
     // 请求超时时间（秒）
    const CONNECT_TIMEOUT = 15;

    /**
     * 基础接口
     */
     // 获取access_token
    const API_ACCESS_TOKEN          = 'https://oapi.dingtalk.com/gettoken?corpid=CORPID&corpsecret=CORPSECRET';
     // 获取 jsapi_ticket
    const API_JSAPI_TICKET          = 'https://oapi.dingtalk.com/get_jsapi_ticket?access_token=ACCESS_TOKE';

    /**
     * 用户管理
     */
     // 通过CODE换取用户身份
    const API_USERID_FROM_CODE      = 'https://oapi.dingtalk.com/user/getuserinfo?access_token=ACCESS_TOKEN&code=CODE';
     // 获取用户信息
    const API_USER_INFO             = 'https://oapi.dingtalk.com/user/get?access_token=ACCESS_TOKEN&userid=zhangsan';
     // 获取管理员列表
    const API_ADMIN_LIST            = 'https://oapi.dingtalk.com/user/get_admin?access_token=ACCESS_TOKEN';

    /**
     * 消息接口
     */
     // 企业会话消息接口
    const API_MESSAGE_SEND          = 'https://oapi.dingtalk.com/message/send?access_token=ACCESS_TOKEN';
     // 发送消息到群会话
    const API_CHAT_SEND             = 'https://oapi.dingtalk.com/chat/send?access_token=ACCESS_TOKEN';
     // 发送普通会话消息接口
    const API_ONVERSATION_SEND      = 'https://oapi.dingtalk.com/message/send_to_conversation?access_token=ACCESS_TOKEN';

    /**
     * CorpId
     *
     * @var string
     */
    public $corpid;

    /**
     * CorpSecret
     *
     * @var string
     */
    public $corpsecret;

    /**
     * AgentId
     * 
     * @var string
     */
    public $agentid;

    /**
     * access_token
     *
     * @var string
     */
    public $access_token;

    /**
     * create Api instance
     *
     * @param  mixed  $config ----> corpid, corpsecret, agentid[, access_token]
     * @return Api
     */
    public function __construct($config)
    {
        $this->corpid = mixed_get($config, 'corpid');
        $this->corpsecret = mixed_get($config, 'corpsecret');
        $this->agentid = mixed_get($config, 'agentid');
        $this->access_token = mixed_get($config, 'access_token');

        $this->client = new GuzzleClient();
    }

    /**
     * get access_token
     *
     * @param  string  $corpid [optional]
     * @param  string  $corpsecret [optional]
     * @return string
     */
    public function getAccessToken($corpid = null, $corpsecret = null)
    {
        $corpid = $corpid ?: $this->corpid;
        $corpsecret = $corpsecret ?: $this->corpsecret;
        $params = ['corpid' => $corpid, 'corpsecret' => $corpsecret];
        $result =  $this->request('GET', self::API_ACCESS_TOKEN, $params);
        $this->access_token = $result->access_token;
        return $result;
    }

    /**
     * get jsapi_ticket
     *
     * @return object
     */
    public function getJsapiTicket()
    {
        $result = $this->request('GET', self::API_JSAPI_TICKET);
        return $result;
    }

    /**
     * get userid from code
     * 
     * @param  string  $code
     * @return object
     */
    public function getUseridFromCode($code)
    {
        $result = $this->request('GET', self::API_USERID_FROM_CODE, compact('code'));
        return $result;
    }

    /**
     * get dingtalk user info
     *
     * @param string $userid
     * @param string $language (optional: zh_CN|en_US)
     *
     * @return object (user object)
     */
    public function getProfile($userid, $language = 'zh_CN')
    {
        $params = ['userid'=>$userid];
        $result = $this->request('GET', self::API_USER_INFO, $params);
        return $result;
    }

    /**
     * send custom message to dingtalk user.
     * @param  array|string  $userids
     * @param  $data  array  refs: dingtalk wiki
     * @return object
     */
    public function sendMessage($userids, $data)
    {
        $data['agentid'] = $this->agentid;
        $data['touser'] = implode('|', (array)$userids);
        $result = $this->request('POST', self::API_MESSAGE_SEND, $data);
        return $result;
    }

    /**
     * packed request method with access_token
     *
     * @param  string  $method:  GET | POST
     * @param  string  $url
     * @param  string  $data
     * @return object
     */
    private function request($method, $url, $data = [])
    {
        $run = function() use ($method, $url, $data)
        {
            $method = strtolower($method);

            // 若是get方法，则将$data里的数据放到query里面去
            $query = ['access_token' => $this->access_token];
            if ($method == 'get') {
                $query = $query + $data;
                $data = null;
            }
            $url = url_make($url, ['query' => $query]);

            if ($method == 'post') {
                $data = json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }

            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];
            $response = $this->client
            ->$method($url, [
                'headers' => $headers,
                'body' => $data,
                'timeout' => self::CONNECT_TIMEOUT,
            ])
            ->getBody();
            $result = json_decode($response);

            self::checkResult($result);
            return $result;
        };
        $result = $this->_autoRetry(function() use($run) {
            return $run();
        });
        return $result;
    }

    /**
     * detect response from dingtalk api
     *
     * @param  object  $result
     * @return void
     */
    public static function checkResult($result)
    {
        if ( ! isset($result->errcode)) {
            return;
        }
        if ($result->errcode === self::RESULT_SUCCESS) {
            return;
        }
        if ($result->errcode === self::RESULT_SYSTEM_BUSY) {
            throw new SystemBusyException($result->errmsg, $result->errcode);
        }
        $invalid_token_codes = [
            self::RESULT_INVALID_TOKEN,
            self::RESULT_INVALID_ACCESS_TOKEN,
            self::RESULT_EXPIRES_ACCESS_TOKEN
        ];
        if (in_array($result->errcode, $invalid_token_codes)) {
            throw new InvalidAccessTokenException($result->errmsg, $result->errcode);
        }
        throw new Exception($result->errmsg, $result->errcode);
    }

    /**
     * 检测返回的状态码，若是繁忙则自动重试(最多3次)
     *
     * @param  Closure  $work
     * @return object
     */
    private function _autoRetry (Closure $work)
    {
        $tries = 0;
        do {
            try {
                $result = call_user_func($work);
                return $result;
            } catch (SystemBusyException $e) {
                $tries++;
                sleep(2);
            }
        } while ($tries < 3);
    }
}
