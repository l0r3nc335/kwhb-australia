<?php

namespace App\Core\v1\Activitylog\Http\Controllers;

use App\Core\v1\Activitylog\Models\Activitylog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActivitylogController extends Controller
{
    public function activityLogs($id)
    {
        return response()->json(
            Activitylog::where('user_id', $id)->orderBy('created_at', 'DESC')->get()
        );
    }
}
