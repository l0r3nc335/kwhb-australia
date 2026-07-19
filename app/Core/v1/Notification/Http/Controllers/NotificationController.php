<?php

namespace App\Core\v1\Notification\Http\Controllers;

use App\Core\v1\Notification\Models\Notification;
use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\ModelRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends AppController
{
    public function __construct(
        protected ModelRepository $repository = new NotificationRepository
    )
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 5);

        return response()->json(
            [
                'notifications' => $this->repository->getAuthUserNotifications()->page($page)->paginate($perPage)->get(),
                'unread_count' => $this->repository->getAuthUserUnreadCount()
            ]
        );
    }

    public function toggleRead($id)
    {
        $notification = Notification::find($id);

        $this->repository->use($notification)->toggleRead();

        return response()->json([
            'message' => 'Success'
        ]);
    }
}
