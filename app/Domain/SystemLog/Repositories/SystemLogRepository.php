<?php

namespace App\Domain\SystemLog\Repositories;

use App\Domain\Common\BaseRepository;
use App\Domain\SystemLog\Model\SystemLog;

class SystemLogRepository extends BaseRepository
{
    public function model(): string
    {
        return SystemLog::class;
    }
}
