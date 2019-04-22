<?php

/**
 * 钉钉配置
 * 
 */

return [
    /**
     * 配置参数，在钉钉开发文档中查看
     */
    'corp_id' => env('DINGTALK_CORP_ID', 'your-corp-id'),
    'agent_id' => env('DINGTALK_AGENT_ID', 'your-agent-id'),
    'app_key' => env('DINGTALK_APP_KEY', 'your-app-key'),
    'app_secret' => env('DINGTALK_APP_SECRET', 'your-app-secret'),
];