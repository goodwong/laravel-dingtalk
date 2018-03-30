<?php

namespace Goodwong\Dingtalk\Services;

use Log;
use Cache;
use Goodwong\Dingtalk\Services\Api;
use Goodwong\Dingtalk\Exceptions\Exception;
use Goodwong\Dingtalk\Exceptions\InvalidAccessTokenException;

/**
 * Api Service
 * 1. check params
 * 4. auto retry when access token is expired
 * 5. pass through access to App\Services\Dingtalk\Api;
 */
class DingtalkService
{
    /**
     * access_token cache key
     */
    const CACHE_KEY_ACCESS_TOKEN = 'dingtalk_access_token';

    /**
     * jsapi_ticket cache key
     */
    const CACHE_KEY_JSAPI_TICKET = 'dingtalk_jsapi_ticket';

    /**
     * create api service instance
     *
     * @return ApiService
     */
    public function __construct()
    {
        $this->agentid = config('dingtalk.agent_id');
        $this->corpid = config('dingtalk.corp_id');
        $this->corpsecret = config('dingtalk.corp_secret');
    }

    /**
     * 若是access_token过期，则自动更新并重试
     *
     * @param  string  $method
     * @param  array   $args
     * @return object
     */
    private function retry($method, $args = [])
    {
        $guards = [
            'getAccessToken'
        ];
        if (in_array($method, $guards)) {
            throw new Exception('该方法无法代理访问，请通过调用->getApi()->access_token获取数据');
        }

        $tried = 0;
        $maxTries = 2;
        do {
            $refresh = !! $tried;
            try {
                // 获取不到access_token，结束
                $api = $this->getApi($refresh);
                $result = call_user_func_array(array($api, $method), $args);
                return $result;
            }
            // 无效的access_token，则重试
            catch (InvalidAccessTokenException $e) {
                if (++$tried < $maxTries) {
                    continue;
                }
                throw $e;
            }
        } while ($tried < $maxTries);
    }

    /**
     * get cached access_token
     * 
     * @return string | null
     */
    private function getAccessToken()
    {
        return Cache::get(self::CACHE_KEY_ACCESS_TOKEN);
    }

    /**
     * cache access_token
     */
    private function cacheAccessToken($access_token)
    {
        Cache::put(self::CACHE_KEY_ACCESS_TOKEN, $access_token, 100);
    }

    /**
     * get api instance
     *
     * @param  boolean  $forceRefresh
     * @return Api
     */
    public function getApi($refresh = false)
    {
        // 有效缓存，返回
        if (isset($this->api) && ! $refresh) {
            return $this->api;
        }

        // 无效的corpid，异常
        if ( ! $this->checkSettings()) {
            return new Exception('invalid settings', -2);
        }

        $access_token = $this->getAccessToken();
        if ( ! isset($this->api)) {
            $this->api = new Api([
                'agentid' => $this->agentid,
                'corpid' => $this->corpid,
                'corpsecret' => $this->corpsecret,
                'access_token' => $access_token,
            ]);
        }

        // access_token有效，且无强制刷新，返回
        if ( ! $refresh && $access_token) {
            return $this->api;
        }

        // 获取access_token成功，返回
        try {
            $this->api->getAccessToken();
            $this->cacheAccessToken($this->api->access_token);

            Log::info('--- get dingtalk access_token success ---');
            return $this->api;
        }
        // 获取失败，异常
        catch (Exception $e) {
            unset($this->api);
            throw $e;
        }
    }

    /**
     * get jsapi ticket
     *
     * @param  boolean  $forceRefresh
     * @return object
     */
    public function getJsapiTicket($refresh = false)
    {
        $ticket = Cache::get(self::CACHE_KEY_JSAPI_TICKET);
        // 有效缓存，返回
        if ($ticket && ! $refresh) {
            return $ticket;
        }

        // 获取jsapi_ticket成功，返回
        try {
            $result = $this->getApi()->getJsapiTicket();
            Cache::put(self::CACHE_KEY_JSAPI_TICKET, $result->ticket, $result->expires_in/60 - 20);

            Log::info('--- get dingtalk jsapi_ticket success ---', (array)$result);
            return $result->ticket;
        }
        // 获取失败，异常
        catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * get jsapi config
     *
     * @param  string  $url
     * @return object
     */
    public function getJsapiConfig($url)
    {
        $jsapi_ticket = $this->getJsapiTicket();

        $agentId = $this->agentid;
        $corpId = $this->corpid;
        $timeStamp = (string)time();
        $nonceStr = md5(rand(100000, 999999) . time());
        $signature = sha1("jsapi_ticket={$jsapi_ticket}&noncestr={$nonceStr}&timestamp={$timeStamp}&url={$url}");
        $type = 0;
        $jsConfig = compact('agentId', 'corpId', 'timeStamp', 'nonceStr', 'signature', 'type');
        return $jsConfig;
    }

    /**
     * get dingtalk profile from code
     * 
     * @param  string  $code
     * @return object
     */
    public function getProfileFromCode($code)
    {
        $result = $this->getUseridFromCode($code);
        $profile = $this->getProfile($result->userid);
        return $profile;
    }

    /**
     * check wechat platform settings
     *
     * @return boolean
     */
    private function checkSettings()
    {
        if ( ! $this->corpid) {
            return false;
        }
        if ( ! $this->corpsecret) {
            return false;
        }
        if ( ! $this->agentid) {
            return false;
        }
        return true;
    }

    /**
     * 隐式代理 Api的方法
     *
     * @param $method string
     * @param $args array
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        return $this->retry($method, $args);
    }
}
