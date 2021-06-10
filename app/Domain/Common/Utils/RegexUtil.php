<?php

namespace App\Domain\Common\Utils;

class RegexUtil
{
    const PHONE = '/^1[3456789][0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/';
    const MONEY_EGT_ZERO = '/(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/';
    const URL = '#(http|https)://(.*\.)?.*\..*#i';
}
