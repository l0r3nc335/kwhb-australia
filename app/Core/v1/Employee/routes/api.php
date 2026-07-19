<?php
use Illuminate\Support\Facades\Route;
use App\Core\v1\Employee\Http\Controllers\EmployeeController;

//    Route::group(['prefix' => 'employee'], function () {
//        Route::post('update_row', [EmployeeController::class, 'update_row']);
//        Route::post('update_emergency_contact', [EmployeeController::class, 'update_emergency_contact']);
//        Route::post('update_medical', [EmployeeController::class, 'update_medical']);
//        Route::post('update_address', [EmployeeController::class, 'update_address']);
//        Route::post('fetch_approvers', [EmployeeController::class, 'fetch_approvers']);
//        Route::post('fetch_line_managers', [EmployeeController::class, 'fetch_line_managers']);
//        Route::post('fetch_employee_by_name', [EmployeeController::class, 'fetch_employee_by_name']);
//        Route::post('fetch_widget_data', [EmployeeController::class, 'fetch_widget_data']);
//        Route::post('multiple_filter', [EmployeeController::class, 'multipleFilter']);
//        Route::post('fetch_employees', [EmployeeController::class, 'fetch_employees']);
//        Route::post('update_attachment', [EmployeeController::class, 'update_attachment']);
//    });


// fetch_employees
//Route::get('/', [EmployeeController::class, 'index']);
//// fetch_employee_by_name
//Route::get('/{id}', [EmployeeController::class, 'item']);
//// fetch_approvers
//Route::get('/{id}/approvers', [EmployeeController::class, 'getApprovers']);
//// fetch_line_managers
//Route::get('/{id}/line-managers', [EmployeeController::class, 'getLineManagers']);
//// (create employee)
//Route::post('/', [EmployeeController::class], 'store');
//// update_row
//Route::put('/{id}', [EmployeeController::class, 'update']);
//// update_medical
//Route::post('/{id}/medical-histories', [EmployeeController::class], 'updateMedicalHistories');
//// update_emergency_contact
//Route::put('/{id}/emergency-contacts', [EmployeeController::class, 'updateEmergencyContact']);
//// update_address
//Route::put('/{id}/addresses', [EmployeeController::class, 'updateAddress']);
//// (archive employee)
//Route::delete('/{id}', [EmployeeController::class, 'archive']);
