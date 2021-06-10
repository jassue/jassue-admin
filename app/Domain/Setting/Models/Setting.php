<?php

namespace App\Domain\Setting\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 * @package App\Domain\Setting\Models
 * @property string key
 * @property string desc
 * @property array values
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $fillable = [
        'key', 'desc', 'values'
    ];

    protected $casts = [
        'values' => 'json'
    ];
}
