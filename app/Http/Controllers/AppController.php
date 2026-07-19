<?php

namespace App\Http\Controllers;
use App\Core\v1\Notification\Repositories\NotificationRepository;
use App\Models\User;
use App\Repositories\ModelRepository;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    protected ?User $authUser = null;
    protected ?AuthorizationService $authService = null;
    protected ?NotificationRepository $notification = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $this->authUser = User::find(Auth::user()->id);
                $this->authService = new AuthorizationService($this->authUser);
                $this->notification = new NotificationRepository;
            }
            return $next($request);
        });
    }
}
