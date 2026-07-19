<?php

use App\Core\v1\Activitylog\Http\Controllers\ActivitylogController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmploymentController;
use App\Http\Controllers\BankInformationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\RoleController;
use App\Myob\Services\Employee\EmployeeServices;
use App\Core\v1\Position\Http\Controllers\PositionController as PositionController2;
use App\Core\v1\Employee\Http\Controllers\EmployeeController as EmployeeController2;
use App\Core\v1\Division\Http\Controllers\DivisionController as DivisionController2;
use App\Core\v1\Year\Http\Controllers\YearController as YearController2;
use App\Core\v1\PayClassification\Http\Controllers\PayClassificationController as PayClassificationController2;
use App\Core\v1\PayRange\Http\Controllers\PayRangeController;
// use App\Core\v1\JobTitle\Http\Controllers\JobTitleController as JobTitleController2;
use App\Core\v1\Location\Http\Controllers\LocationController as LocationController2;
use App\Core\v1\Role\Http\Controllers\RoleController as RoleController2;
use App\Core\v1\Employment\Http\Controllers\EmploymentController as EmploymentController2;
use App\Core\v1\Timesheet\Http\Controllers\TimesheetController;
use App\Core\v1\Leave\Http\Controllers\LeaveController as LeaveController2;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\EmployeeComplianceController;
use App\Http\Controllers\EmployeeVaccinationController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\VaccinationController;
use App\Core\v1\Adminsetting\Http\Controllers\AdminSettingController;
use App\Core\v1\Roster\Http\Controllers\RosterController;
use App\Core\v1\Contract\Http\Controllers\ContractController;
use App\Core\v1\Template\Http\Controllers\TemplateController;
use App\Core\v1\Template\Http\Controllers\TemplateFieldController;
use App\Core\v1\Notification\Http\Controllers\NotificationController;
use App\Core\v1\Resource\Http\Controllers\ResourceController;
use App\Core\v1\Report\Http\Controllers\ReportController;
use App\Core\v1\Expense\Http\Controllers\ExpenseController;
use App\Core\v1\Acknowledgement\Http\Controllers\AcknowledgementController;
use App\Core\v1\Allowance\Http\Controllers\AllowanceController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::group(['middleware' => ['web']], function () {
//     Route::post('auth/login', [UserController::class, 'login']);
//     Route::post('auth/logout', [UserController::class, 'logout']);
// });
Route::group(
    array('prefix' => 'auth'),
    function () {
        Route::post('login', array(UserController::class, 'login'))->middleware('throttle:5,1');
        Route::middleware('auth:sanctum')->get('user', array(UserController::class, 'me'));
        Route::middleware('auth:sanctum')->post('logout', array(UserController::class, 'logout'));
        Route::post('forgot-password', array(UserController::class, 'forgotPassword'));
        Route::post('reset-password', array(UserController::class, 'resetPassword'));
    }
);
Route::get('/notes/pdf/{file}', [NotesController::class, 'notesPdf']);
Route::get('/compliance/pdf/{file}', [EmployeeComplianceController::class, 'employeeCompliancePdf']);
Route::get('/vaccination/pdf/{file}', [EmployeeVaccinationController::class, 'employeeVaccinationPdf']);


// Refactored Endpoints
Route::group(['middleware' => ['auth:sanctum'], 'prefix' =>  'v1'], function () {
    // Users
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/', [\App\Core\v1\Dashboard\Http\Controllers\DashboardController::class, 'index']);
    });

    // Users
    Route::group(['prefix' => 'users'], function () {
        Route::get('/{id}/employee-data', [\App\Core\v1\User\Http\Controllers\UserController::class, 'getEmployeeData']);
        Route::get('/', [\App\Core\v1\User\Http\Controllers\UserController::class, 'index']);
        Route::get('/{id}/logs', [ActivitylogController::class, 'activityLogs']);
    });

    // Divisions

    /* TODO: remove this once stable anad tested the combined prefix
     * Route::group(['prefix' => 'divisions'], function () {
        Route::post('/create', [DivisionController::class, 'store']);
        Route::post('/update', [DivisionController::class, 'update']);
        Route::delete('/delete/{id}', [DivisionController::class, 'destroy']);
        Route::post('/list', [DivisionController::class, 'division']);
    });*/

    Route::group(['prefix' => 'divisions'], function () {
        Route::post('/create', [DivisionController::class, 'store']);
        Route::post('/update', [DivisionController::class, 'update']);
        Route::delete('/delete/{id}', [DivisionController::class, 'destroy']);
        Route::post('/list', [DivisionController::class, 'division']);

        Route::get('/get_paginated_division', [DivisionController2::class, 'getPaginatedDivision']);
        Route::get('/', [DivisionController2::class, 'index']);
        Route::get('/active', [DivisionController2::class, 'active']);
        Route::get('/{id}/line-managers', [DivisionController2::class, 'getLineManagers']);
        Route::post('/', [DivisionController2::class, 'store']);
        Route::post('/{id}/status', [DivisionController2::class, 'updateStatus']);
        Route::get('/{id}/history', [DivisionController2::class, 'getHistory']);
    });

    Route::group(['prefix' => 'year'], function () {
        Route::get('/', [YearController2::class, 'index']);
        Route::post('/', [YearController2::class, 'store']);
    });    


    Route::group(['prefix' => 'pay-classifications'], function () {
        // Paginated & filtered listing
        Route::get('/get_paginated_classifications', [PayClassificationController2::class, 'getPaginatedClassifications']);
        
        // List all
        Route::get('/', [PayClassificationController2::class, 'index']);
        
        // List all active
        Route::get('/active', [PayClassificationController2::class, 'index']);


        // Retrieve one by ID
        Route::get('/{id}', [PayClassificationController2::class, 'show']);
        
        // Create new
        Route::post('/', [PayClassificationController2::class, 'store']);
        
        // Create new
        Route::post('/csv', [PayClassificationController2::class, 'storeCSV']);


        // Update existing
        Route::put('/{id}', [PayClassificationController2::class, 'update']);
        
        // Update only status
        Route::post('/{id}/status', [PayClassificationController2::class, 'updateStatus']);

        Route::get('/{id}/history', [PayClassificationController2::class, 'getHistory']);

        Route::post('/{id}/duplicate', [PayClassificationController2::class, 'duplicate']);

    });   
    
    Route::group(['prefix' => 'pay-ranges'], function () {
        // Paginated & filtered listing
        Route::get('/get_paginated_ranges', [PayRangeController::class, 'getPaginatedRanges']);

        // List all
        Route::get('/', [PayRangeController::class, 'index']);

        // List all active
        Route::get('/active', [PayRangeController::class, 'indexActive']);

        // Create new
        Route::post('/', [PayRangeController::class, 'store']);

        Route::post('/csv', [PayRangeController::class, 'storeCSV']);


        // Update existing
        Route::put('/{id}', [PayRangeController::class, 'update']);

        // Update only status
        Route::post('/{id}/status', [PayRangeController::class, 'updateStatus']);

        // Get history
        Route::get('/{id}/history', [PayRangeController::class, 'getHistory']);

        // Duplicate
        Route::post('/{id}/duplicate', [PayRangeController::class, 'duplicate']);


        Route::get('/years', [PayRangeController::class, 'years']);
    });    

    /*
     * TODO: Remove duplicate once below prefix is stable
     * Route::group(['prefix' => 'job_titles'], function () {
        Route::post('/create', [PositionController2::class, 'store']);
        Route::post('/update', [PositionController2::class, 'update']);
        Route::delete('/delete/{id}', [PositionController2::class, 'destroy']);
        Route::post('/list', [PositionController2::class, 'jobTitle']);
    });*/


    Route::group(['prefix' => 'job_titles'], function () {
        Route::post('/create', [PositionController2::class, 'store']);
        Route::post('/update', [PositionController2::class, 'update']);
        Route::delete('/delete/{id}', [PositionController2::class, 'destroy']);
        Route::post('/list', [PositionController2::class, 'jobTitle']);

        Route::get('/get_paginated_job_title', [PositionController2::class, 'getPaginatedJobTitle']);
        Route::get('/get_active_jobs_for_division', [PositionController2::class, 'getPaginatedJobTitleForDivision']);
        Route::get('/get_active_jobs', [PositionController2::class, 'getActiveJobTitle']);
        Route::get('/', [PositionController2::class, 'index']);
        Route::post('/by_names', [PositionController2::class, 'getByNames']);
        Route::get('/{id}/line-managers', [PositionController2::class, 'getLineManagers']);
        Route::post('/', [PositionController2::class, 'store']);
        Route::post('/{id}/status', [PositionController2::class, 'updateStatus']);
        Route::get('/{id}/history', [PositionController2::class, 'getHistory']);
    });

    Route::group(['prefix' => 'allowances'], function () {
        Route::post('/create', [AllowanceController::class, 'store']);
        Route::post('/update/{id}', [AllowanceController::class, 'update']);
        Route::post('/update_statuses', [AllowanceController::class, 'update_statuses']);
        Route::delete('/delete/{id}', [AllowanceController::class, 'destroy']); // optional if implemented
        Route::post('/list', [AllowanceController::class, 'index']); // optional if needed like `jobTitle`
        Route::post('/{id}/status', [AllowanceController::class, 'updateStatus']);

        Route::get('/get_paginated_allowance', [AllowanceController::class, 'getPaginatedAllowance']);
        Route::get('/', [AllowanceController::class, 'index']);
        Route::get('/active', [AllowanceController::class, 'getActiveAllowances']);
        Route::post('/package', [AllowanceController::class, 'getAllowancePackage']);
        Route::get('/{id}/history', [AllowanceController::class, 'getHistory']);
    });


    // // Locations
    // Route::group(['prefix' => 'locations'], function () {
    //     Route::get('/', [LocationController2::class, 'index']);
    //     Route::post('/', [LocationController2::class, 'store']);
    // });

    // Divisions
    Route::group(['prefix' => 'locations'], function () {
        Route::get('/get_paginated_location', [LocationController2::class, 'getPaginatedLocation']);
        Route::get('/', [LocationController2::class, 'index']);
        Route::post('/', [LocationController2::class, 'store']);
        Route::post('/{id}/status', [LocationController2::class, 'updateStatus']);
        Route::get('/{id}/history', [LocationController2::class, 'getHistory']);
    });

    // Roles
    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', [RoleController2::class, 'index']);
        Route::get('/all', [RoleController2::class, 'all']);
        Route::post('/{id}', [RoleController2::class, 'addPermission']);
        Route::delete('/{id}', [RoleController2::class, 'removePermission']);
    });

    // Employments
    Route::group(['prefix' => 'employments'], function () {
        Route::get('/get_paginated_employment_type', [EmploymentController2::class, 'getPaginatedEmploymentType']);
        Route::get('/', [EmploymentController2::class, 'index']);
        Route::post('/', [EmploymentController2::class, 'store']);
        Route::post('/{id}/status', [EmploymentController2::class, 'updateStatus']);
        Route::get('/{id}/history', [EmploymentController2::class, 'getHistory']);
    });
    
    // Employees
    Route::group(['prefix' => 'employees'], function () {
        // fetch_employees
        Route::get('/', [EmployeeController2::class, 'index']);
        Route::get('/all', [EmployeeController2::class, 'all']);
        Route::get('/job-titles', [EmployeeController2::class, 'getJobTitles']);
        Route::get('/statuses', [EmployeeController2::class, 'getStatuses']);
        //// fetch_approvers
        Route::get('/{id}/approvers', [EmployeeController2::class, 'getApprovers']);
        // fetch_line_managers
        Route::get('/{id}/line-managers', [EmployeeController2::class, 'getLineManagers']);
        // Fetch job titles
        // (create employee)
        Route::post('/', [EmployeeController2::class], 'store');
        // update_row
        Route::put('/{id}', [EmployeeController2::class, 'update']);
        // update_medical
        Route::put('/{id}/medical-histories', [EmployeeController2::class], 'updateMedicalHistories');
        // update_emergency_contact
        Route::put('/{id}/emergency-contacts', [EmployeeController2::class, 'updateEmergencyContact']);
        // update_address
        Route::put('/{id}/addresses', [EmployeeController2::class, 'updateAddress']);
        // (archive employee)
        Route::delete('/{id}', [EmployeeController2::class, 'archive']);
    });
    // Timesheets
    Route::group(['prefix' => 'timesheets'],  function () {
        Route::get('/', [TimesheetController::class, 'index']);
        Route::get('/date-categories', [TimesheetController::class, 'getDateCategories']);
        Route::get('/period-dates', [TimesheetController::class, 'getPeriodDates']);
        Route::get('/categories', [TimesheetController::class, 'categories']);
        Route::get('/templates', [TimesheetController::class, 'indexTemplate']);
        Route::get('/submitted', [TimesheetController::class, 'getSubmittedTimesheets']);
        Route::get('/approved', [TimesheetController::class, 'getApprovedTimesheets']);
        Route::get('/unsubmitted', [TimesheetController::class, 'getUnSubmittedTimesheets']);
        Route::post('/', [TimesheetController::class, 'store']);
        Route::post('/submit', [TimesheetController::class, 'submitPeriod']);
        Route::post('/return', [TimesheetController::class, 'returnWeekPeriod']);
        Route::post('/approve', [TimesheetController::class, 'approveWeekPeriod']);
        Route::post('/templates', [TimesheetController::class, 'storeTemplate']);
        Route::put('/templates', [TimesheetController::class, 'applyTemplate']);
        Route::put('/templates/{id}', [TimesheetController::class, 'updateTemplate']);
        Route::put('/{id}', [TimesheetController::class, 'update']);
        Route::post('/{id}/submit', [TimesheetController::class, 'submit']);
        Route::post('/{id}/approve', [TimesheetController::class, 'approve']);
        Route::post('/{id}/return', [TimesheetController::class, 'return']);
        Route::delete('/{id}', [TimesheetController::class, 'deleteEntry']);
        Route::delete('/category', [TimesheetController::class, 'deleteCategoryEntries']);
        Route::delete('/templates/{id}', [TimesheetController::class, 'deleteTemplate']);
        Route::delete('/', [TimesheetController::class, 'delete']);
    });
    // Leaves
    Route::group(['prefix' => 'leaves'], function () {
        Route::get('/', [LeaveController2::class, 'index']);
        Route::get('/details', [LeaveController2::class, 'getLeaveDetails']);
        Route::get('/calendar', [LeaveController2::class, 'getCalendarData']);
        Route::get('/types', [LeaveController2::class, 'getTypes']);
        Route::get('/statuses', [LeaveController2::class, 'getStatuses']);
        Route::get('/credits', [LeaveController2::class, 'getCredits']);
        Route::post('/', [LeaveController2::class, 'store']);
        Route::post('/approve', [LeaveController2::class, 'approve']);
        Route::post('/decline', [LeaveController2::class, 'decline']);
        Route::post('/cancel', [LeaveController2::class, 'cancel']);
        Route::put('/', [LeaveController2::class, 'update']);
        Route::delete('/', [LeaveController2::class, 'delete']);
    });

    // Locations



    // Notes
    Route::group(['prefix' => 'notes'], function () {
        Route::post('/create', [NotesController::class, 'create']);
        Route::post('/update', [NotesController::class, 'update']);
        Route::delete('/delete/{id}', [NotesController::class, 'delete']);
        Route::get('/user', [NotesController::class, 'userNotes']);
    });

    // Vaccination
    Route::group(['prefix' => 'vaccination'], function () {
        Route::get('/', [VaccinationController::class, 'index']);
        Route::post('/create', [VaccinationController::class, 'create']);
        Route::post('/update', [VaccinationController::class, 'update']);
        Route::delete('/delete/{id}', [VaccinationController::class, 'delete']);
        Route::post('/list', [VaccinationController::class, 'vaccination']);
        Route::post('/role', [VaccinationController::class, 'getVaccinationsByRole']);
        Route::post('/employee/create', [EmployeeVaccinationController::class, 'create']);
        Route::post('/employee/update', [EmployeeVaccinationController::class, 'update']);
        Route::delete('/employee/delete/{id}', [EmployeeVaccinationController::class, 'delete']);
        Route::post('/employee', [EmployeeVaccinationController::class, 'employee_vaccination']);
        Route::post('/employee/list', [EmployeeVaccinationController::class, 'vaccination']);
        Route::post('/employee/approval', [EmployeeVaccinationController::class, 'approval']);
    });

    // Compliance
    Route::group(['prefix' => 'compliance'], function () {
        Route::get('/', [ComplianceController::class, 'index']);
        Route::post('/create', [ComplianceController::class, 'create']);
        Route::post('/update', [ComplianceController::class, 'update']);
        Route::delete('/delete/{id}', [ComplianceController::class, 'delete']);
        Route::post('/list', [ComplianceController::class, 'compliance']);
        Route::post('/role', [ComplianceController::class, 'getComplianceByRole']);
        Route::post('/employee/create', [EmployeeComplianceController::class, 'create']);
        Route::post('/employee/update', [EmployeeComplianceController::class, 'update']);
        Route::delete('/employee/delete/{id}', [EmployeeComplianceController::class, 'delete']);
        Route::post('/employee', [EmployeeComplianceController::class, 'employee_compliance']);
        Route::post('/employee/list', [EmployeeComplianceController::class, 'compliance']);
        Route::post('/employee/approval', [EmployeeComplianceController::class, 'approval']);
    });

    // Employments
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/roles-and-permissions', [AdminSettingController::class, 'getRolePermissionSettings']);
        Route::get('/roles-with-users', [AdminSettingController::class, 'getRolesWithUsers']);
        Route::get('/divisions', [AdminSettingController::class, 'getDivisions']);
        Route::get('/locations', [AdminSettingController::class, 'getLocations']);
        Route::get('/job-titles', [AdminSettingController::class, 'getJobTitles']);
        Route::get('/employments', [AdminSettingController::class, 'getEmployments']);
        Route::get('/permission-navigations', [AdminSettingController::class, 'getPermissionSettingNavigations']);
        Route::put('/roles-and-permissions', [AdminSettingController::class, 'updateRolePermissions']);
    });

    // Contracts
    Route::group(['prefix' => 'contracts'], function () {
        Route::get('/', [ContractController::class, 'index']);
        Route::get('/contract', [ContractController::class, 'getContract']);
        Route::get('/contract_offer', [ContractController::class, 'getContractOfOffer']);
        Route::get('/expiries', [ContractController::class, 'expiries']);
        Route::get('/roles', [ContractController::class, 'getContractJobTitle']);
        Route::get('/statuses', [ContractController::class, 'getStatuses']);
        Route::post('save-signature', [ContractController::class, 'saveSignature'])->name('save-signature');
        Route::post('/store', [ContractController::class, 'store']);
        Route::get('/create/{templateId}', [ContractController::class, 'create']);
        Route::post('/create-contract', [ContractController::class, 'createContract']);
        Route::get('/edit/{id}', [ContractController::class, 'edit']);
        Route::post('/update', [ContractController::class, 'updateContract']);
        Route::post('/update-status', [ContractController::class, 'updateContractStatus']);
        Route::post('/duplicate', [ContractController::class, 'duplicateContract']);
        Route::post('/reject', [ContractController::class, 'rejectContract']);
        Route::delete('/{id}', [ContractController::class, 'deleteContract']);
    });


    // Contract Version History
    Route::group(['prefix' => 'contract-version-history'], function () {
        Route::get('/', [ContractController::class, 'getcontractVersionHistory']);
        Route::post('/contract-history', [ContractController::class, 'getContractHistory']);
    });


    // Contract Templates
    Route::group(['prefix' => 'templates'], function () {
        Route::get('/{type}', [TemplateController::class, 'index']);
        Route::get('/{type}/{status}', [TemplateController::class, 'getByTypeAndStatus']);
        Route::get('/by-type-owner/{type}/{ownerEmployeeId}', [TemplateController::class, 'getByTypeAndOwnerEmployeeId']);
        Route::get('/create', [TemplateController::class, 'create']);
        Route::post('/store', [TemplateController::class, 'store']);
        Route::post('/duplicate', [TemplateController::class, 'duplicate']);
        Route::post('/update/{id}', [TemplateController::class, 'updateTemplate']);
        Route::get('/edit/{id}', [TemplateController::class, 'edit']);
        Route::put('/update-status/{id}', [TemplateController::class, 'updateTemplateStatus']);
        Route::delete('/{id}', [TemplateController::class, 'deleteTemplate']);
    });

    Route::group(['prefix' => 'shared-templates'], function () {
        Route::get('/', [TemplateController::class, 'sharedTemplate']);
        Route::get('/create/{templateId}', [TemplateController::class, 'creatSharedTemplate']);
        Route::post('/store/{templateId}', [TemplateController::class, 'storeSharedTemplate']);
        Route::delete('/{id}', [TemplateController::class, 'deleteSharedTemplate']);
    });


    // Templates Field
    Route::group(['prefix' => 'template-fields'], function () {
        Route::get('/', [TemplateController::class, 'templateFields']);
    });

    // Template Version History
    Route::group(['prefix' => 'template-version-history'], function () {
        Route::get('/{templateId}', [TemplateController::class, 'templateVersionHistory']);
    });

    // Rosters
    Route::group(['prefix' => 'rosters'], function () {
        Route::get('/', [RosterController::class, 'index']);
        Route::get('/templates', [RosterController::class, 'getTemplates']);
        Route::get('/shift-types', [RosterController::class, 'getShiftTypes']);
        Route::get('/employees/{locationId}', [RosterController::class, 'getLocationEmployees']);
        Route::get('/{id}/shifts', [RosterController::class, 'getShifts']);
        Route::post('/employees', [RosterController::class, 'addRosterEmployee']);
        Route::delete('/employees', [RosterController::class, 'removeRosterEmployee']);
        Route::post('/staff', [RosterController::class, 'addStaff']);
        Route::post('/shifts', [RosterController::class, 'addShift']);
        Route::post('/templates', [RosterController::class, 'addTemplate']);
        Route::put('/shifts/{id}', [RosterController::class, 'updateShift']);
        Route::delete('/staff', [RosterController::class, 'removeStaff']);
        Route::delete('/shift', [RosterController::class, 'removeShift']);
        Route::delete('/templates', [RosterController::class, 'deleteTemplate']);
    });

    Route::group(['prefix' => 'resources'], function () {
        Route::get('/', [ResourceController::class, 'index']);
        Route::get('/acknowledgement-required', [ResourceController::class, 'indexAcknowledgementRequired']);
        Route::get('/categories', [ResourceController::class, 'categories']);
        Route::get('/fetchTags', [ResourceController::class, 'fetchTags']);
        Route::post('/', [ResourceController::class, 'store']);
        Route::get('/{id}/tags', [ResourceController::class, 'getTagsByResource']);
        Route::post('/update/{id}', [ResourceController::class, 'update']);
        Route::post('/update_privacy/{id}', [ResourceController::class, 'updatePrivacy']);
        Route::post('/categories', [ResourceController::class, 'storeCategory']);
        Route::post('/tags', [ResourceController::class, 'storeTag']);
        Route::delete('/{id}', [ResourceController::class, 'delete']);

    });

    Route::group(['prefix' => 'acknowledgements'], function () {
        Route::get('/', [AcknowledgementController::class, 'index']);               // List all (optionally filter by resource_id)
        Route::get('/{id}', [AcknowledgementController::class, 'show']);           // Show single
        Route::post('/', [AcknowledgementController::class, 'store']);             // Create new
        Route::post('/update/{id}', [AcknowledgementController::class, 'update']); // Update
        Route::delete('/{id}', [AcknowledgementController::class, 'delete']);      // Delete
    });        

    // Notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/{id}/toggle-read', [NotificationController::class, 'toggleRead']);
    });

    // Reports
    Route::group(['prefix' => 'reports'], function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/graph', [ReportController::class, 'indexGraph']);
    });

    // Expenses
    Route::group(['prefix' => 'expenses'], function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::get('/get_codes', [ExpenseController::class, 'get_codes']);
        Route::post('/', [ExpenseController::class, 'store']);
        Route::put('/{id}', [ExpenseController::class, 'update']);
        Route::put('/update_expense/{id}', [ExpenseController::class, 'update_expense']);
    });
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::resource('attachment', AttachmentController::class);
    Route::resource('leave', LeaveController::class);
    Route::resource('leave_type', LeaveTypeController::class);
    Route::resource('leave_balance', LeaveBalanceController::class);
    Route::resource('division', DivisionController::class);
    // Route::resource('contract', ContractController::class);
    Route::resource('employee', EmployeeController::class);
    Route::resource('location', LocationController::class);
    Route::resource('employment', EmploymentController::class);
    Route::resource('bank_info', BankInformationController::class);
    Route::resource('role', RoleController::class);


    Route::group(['prefix' => 'leave_balance'], function () {
        Route::post('fetch_leave_balance', [LeaveBalanceController::class, 'fetchLeaveBalance']);
    });
    Route::group(['prefix' => 'leave'], function () {
        Route::get('fetch_leave_by_approver/{employee_id}', [LeaveController::class, 'fetchLeaveByApprover']);
        Route::post('update_row', [LeaveController::class, 'update_row']);
        Route::post('multiple_filter', [LeaveController::class, 'multipleFilter']);
    });
    Route::group(['prefix' => 'employee'], function () {
        Route::post('update_row', [EmployeeController::class, 'update_row']);
        Route::post('update_user', [EmployeeController::class, 'update_user']);
        Route::post('update_emergency_contact', [EmployeeController::class, 'update_emergency_contact']);
        Route::post('update_medical', [EmployeeController::class, 'update_medical']);
        Route::post('update_address', [EmployeeController::class, 'update_address']);
        Route::post('fetch_approvers', [EmployeeController::class, 'fetch_approvers']);
        Route::post('fetch_line_managers', [EmployeeController::class, 'fetch_line_managers']);
        Route::post('fetch_employee_by_name', [EmployeeController::class, 'fetch_employee_by_name']);
        Route::post('fetch_widget_data', [EmployeeController::class, 'fetch_widget_data']);
        Route::post('multiple_filter', [EmployeeController::class, 'multipleFilter']);
        Route::post('fetch_employees', [EmployeeController::class, 'fetch_employees']);
        Route::post('update_attachment', [EmployeeController::class, 'update_attachment']);
    });

    Route::group(['prefix' => 'myob'], function () {
        Route::get('contact/employee', [EmployeeServices::class, 'list']);
        Route::get('fetch_employee_by_display_id/{displayID}', [EmployeeServices::class, 'fetchEmployeeByDisplayID']);
    });

    // Locations
    Route::group(['prefix' => 'locations'], function () {
        Route::post('/create', [LocationController::class, 'store']);
        Route::post('/update', [LocationController::class, 'update']);
        Route::delete('/delete/{id}', [LocationController::class, 'destroy']);
        Route::post('/list', [LocationController::class, 'location']);
        Route::get('/active', [LocationController::class, 'activeIndex']);
    });

    Route::group(['prefix' => 'employments'], function () {
        Route::post('/create', [EmploymentController::class, 'store']);
        Route::post('/update', [EmploymentController::class, 'update']);
        Route::delete('/delete/{id}', [EmploymentController::class, 'destroy']);
        Route::post('/list', [EmploymentController::class, 'employment']);
        Route::get('/active', [EmploymentController2::class, 'indexActive']);

    });

    Route::group(['prefix' => 'positions'], function () {
        Route::get('/', [PositionController::class, 'index']);
        Route::get('/active', [PositionController::class, 'activeIndex']);
        Route::post('/create', [PositionController::class, 'store']);
        Route::post('/update', [PositionController::class, 'update']);
        Route::delete('/delete/{id}', [PositionController::class, 'destroy']);
        Route::get('/list', [PositionController::class, 'position']);
        Route::post('/employees', [PositionController::class, 'employees']);
    });
});
