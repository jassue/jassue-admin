<?php

namespace App\Domain\SystemLog\Model;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'operator_id', 'operator_name', 'content', 'client_ip', 'user_agent'
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
