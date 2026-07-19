<?php

namespace App\Core\v1\Activitylog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activitylog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    const TYPE_INFO = 1;
    const TYPE_SUCCESS = 2;
    const TYPE_WARNING = 3;
    const TYPE_ERROR = 4;

    const RESOURCE_USER = 1;
    const RESOURCE_LEAVE = 2;
    const RESOURCE_TIMESHEET = 3;

    const ACTION_CREATE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_ARCHIVE = 3;
    const ACTION_DELETE = 4;
    const ACTION_PROCESS = 5;
}
