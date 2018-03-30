<?php

namespace Goodwong\Dingtalk\Handlers;

use Goodwong\Dingtalk\Events\DingtalkUserCreated;
use Goodwong\Dingtalk\Repositories\DingtalkUserRepository;

class DingtalkHandler
{
    /**
     * construct
     * 
     * @param  DingtalkUserRepository  $dingtalkUserRepository
     * @return void
     */
    public function __construct(DingtalkUserRepository $dingtalkUserRepository)
    {
        $this->dingtalkUserRepository = $dingtalkUserRepository;
    }

    /**
     * create
     * 
     * @param  array  $info
     * @return DingtalkUser
     */
    public function create($info)
    {
        $dingtalkUser = $this->dingtalkUserRepository->create($info);

        event(new DingtalkUserCreated($dingtalkUser));

        return $dingtalkUser;
    }
}
