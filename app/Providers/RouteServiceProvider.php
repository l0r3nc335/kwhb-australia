<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {

            // External Api Route
            Route::middleware('external_api')
                ->prefix('external_api')
                ->group(base_path('routes/external_api.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

			 // Employee Module
			Route::middleware('api')
				->prefix('api/v1/employees')
				->group(base_path('app/Core/v1/Employee/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Employee/routes/web.php'));

			 // Division Module
			Route::middleware('api')
				->prefix('api/v1/divisions')
				->group(base_path('app/Core/v1/Division/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Division/routes/web.php'));

			 // Location Module
			Route::middleware('api')
				->prefix('api/v1/locations')
				->group(base_path('app/Core/v1/Location/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Location/routes/web.php'));

			 // Role Module
			Route::middleware('api')
				->prefix('api/v1/roles')
				->group(base_path('app/Core/v1/Role/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Role/routes/web.php'));

			 // Employment Module
			Route::middleware('api')
				->prefix('api/v1/employments')
				->group(base_path('app/Core/v1/Employment/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Employment/routes/web.php'));

			 // Leave Module
			Route::middleware('api')
				->prefix('api/v1/leaves')
				->group(base_path('app/Core/v1/Leave/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Leave/routes/web.php'));

			 // Bank Module 
			Route::middleware('api')
				->prefix('api/v1/banks')
				->group(base_path('app/Core/v1/Bank/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Bank/routes/web.php'));

			 // Timesheet Module 
			Route::middleware('api')
				->prefix('api/v1/timesheets')
				->group(base_path('app/Core/v1/Timesheet/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Timesheet/routes/web.php'));

			 // Activitylog Module 
			Route::middleware('api')
				->prefix('api/v1/activitylogs')
				->group(base_path('app/Core/v1/Activitylog/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Activitylog/routes/web.php'));

			 // User Module 
			Route::middleware('api')
				->prefix('api/v1/users')
				->group(base_path('app/Core/v1/User/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/User/routes/web.php'));

			 // Permission Module 
			Route::middleware('api')
				->prefix('api/v1/permissions')
				->group(base_path('app/Core/v1/Permission/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Permission/routes/web.php'));

			 // Adminsetting Module 
			Route::middleware('api')
				->prefix('api/v1/adminsettings')
				->group(base_path('app/Core/v1/Adminsetting/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Adminsetting/routes/web.php'));

			 // Dashboard Module 
			Route::middleware('api')
				->prefix('api/v1/dashboards')
				->group(base_path('app/Core/v1/Dashboard/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Dashboard/routes/web.php'));

			 // Roster Module 
			Route::middleware('api')
				->prefix('api/v1/rosters')
				->group(base_path('app/Core/v1/Roster/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Roster/routes/web.php'));

			 // Notification Module 
			Route::middleware('api')
				->prefix('api/v1/notifications')
				->group(base_path('app/Core/v1/Notification/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Notification/routes/web.php'));

			 // Resource Module 
			Route::middleware('api')
				->prefix('api/v1/resources')
				->group(base_path('app/Core/v1/Resource/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Resource/routes/web.php'));

			 // Report Module 
			Route::middleware('api')
				->prefix('api/v1/reports')
				->group(base_path('app/Core/v1/Report/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Report/routes/web.php'));

			 // Expense Module 
			Route::middleware('api')
				->prefix('api/v1/expenses')
				->group(base_path('app/Core/v1/Expense/routes/api.php'));
			Route::middleware('web')
				->group(base_path('app/Core/v1/Expense/routes/web.php'));


        });
    }
}
