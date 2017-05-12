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
    'corp_secret' => env('DINGTALK_CORP_SECRET', 'your-corp-secret'),
    'agent_id' => env('DINGTALK_AGENT_ID', 'your-agent-id'),
];