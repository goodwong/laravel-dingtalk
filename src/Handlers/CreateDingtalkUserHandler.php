<?php

namespace Goodwong\LaravelDingtalk\Handlers;

use Goodwong\LaravelDingtalk\Events\DingtalkUserCreated;
use Goodwong\LaravelDingtalk\Repositories\DingtalkUserRepository;

class CreateDingtalkUserHandler
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