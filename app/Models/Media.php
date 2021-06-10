<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Media
 * @package App\Domain\Common\Models
 * @property string driver_type
 * @property string src_type
 * @property string src
 */
class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_type', 'src_type', 'src'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'pivot'
    ];
}
