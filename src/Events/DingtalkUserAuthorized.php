<?php

namespace Goodwong\Dingtalk\Events;

use Goodwong\Dingtalk\Entities\DingtalkUser;
use Illuminate\Queue\SerializesModels;

class DingtalkUserAuthorized
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
