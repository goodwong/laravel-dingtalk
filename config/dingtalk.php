<?php

/**
 * 钉钉配置
 * 
 */

return [
    /**
     * 配置参数，在钉钉开发文档中查看
     */
    'corp_id' => env('DINGTALK_CORP_ID', 'your-app-id'),
    'corp_secret' => env('DINGTALK_CORP_SECRET', 'your-app-secret'),
    'agent_id' => env('DINGTALK_AGENT_ID', 'your-app-secret'),
];