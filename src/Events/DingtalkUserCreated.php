<?php

namespace Goodwong\LaravelDingtalk\Events;

use Goodwong\LaravelDingtalk\Entities\DingtalkUser;
use Illuminate\Queue\SerializesModels;

class DingtalkUserCreated
{
    use SerializesModels;

    public $dingtalkUser;

    /**
     * Create a new event instance.
     *
     * @param  DingtalkUser  $dingtalkUser
     * @return void
     */
    public function __construct(DingtalkUser $dingtalkUser)
    {
        $this->dingtalkUser = $dingtalkUser;
    }
}