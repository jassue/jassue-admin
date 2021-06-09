<?php

namespace App\Domain\Common;

use Illuminate\Contracts\Support\Arrayable;
use MabeEnum\Enum;

abstract class BaseEnum extends Enum implements Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['name' => $this->getName(), 'value' => $this->getValue()];
    }
}
