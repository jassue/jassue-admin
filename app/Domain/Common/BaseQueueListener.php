<?php

namespace App\Domain\Common;

use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseQueueListener implements ShouldQueue
{
    public $queue = 'listeners';
}
