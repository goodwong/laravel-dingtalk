<?php

namespace Goodwong\Dingtalk\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;

class DingtalkUserRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return \Goodwong\Dingtalk\Entities\DingtalkUser::class;
    }

    /**
     * Retrieve first data of repository, or create new Entity
     *
     * @param array $attributes
     * @param array $additions
     * @return mixed
     */
    public function firstOrCreate(array $attributes = [], array $additions = [])
    {
        $this->applyCriteria();
        $this->applyScope();
        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);
        $model = $this->model->firstOrCreate($attributes, $additions);
        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();
        return $this->parserResult($model);
    }
}
