<?php

namespace App\Services;

use App\Repositories\Interfaces\BaseRepositoryInterface;

abstract class BaseService
{
    public function __construct(protected BaseRepositoryInterface $repository) {}
}
