<?php

namespace App\Core\v1\Permission\Models;

use App\Core\v1\Role\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    // Main Features
    const FEATURE_DASHBOARD = 1;
    const FEATURE_PEOPLE = 2;
    const FEATURE_RECRUITMENT = 3;
    const FEATURE_OPERATIONAL = 4;
    const FEATURE_TRAVEL = 5;
    const FEATURE_RESOURCES = 6;
    const FEATURE_REPORTING = 7;
    const FEATURE_ADMIN_SETTINGS = 8;

    // Sub features
    const SUB_FEATURES = [
        self::FEATURE_PEOPLE => [
            'leave_management' => 1,
            'employee_dashboard' => 2,
            'employee_dashboard_employee_section' => 3,
            'employee_profile_contact_section' => 4,
            'employee_profile_personal_detail' => 5,
            'employee_info_emergency_contact' => 6,
            'employee_info_medical' => 7,
            'employee_certification_vaccinations' => 8,
            'employee_certification_compliance' => 9,
            'employee_pay_bank_information' => 10,
            'employee_pay_tfn' => 11,
            'employee_super_info' => 12,
            'employee_leave' => 13,
            'employee_contract' => 14,
            'leave_management_leave_request' => 15,
            'leave_management_leave_requestz' => 20,
            'leave_management_leave_calendar' => 16,
            'leave_management_apply_wizard' => 17,
            'employee_activity_logs' => 18,
            'employee_notes' => 19
        ],
        self::FEATURE_RECRUITMENT => [
            'contract' => 1,
        ],
        self::FEATURE_OPERATIONAL => [
            'timesheets' => 1,
            'timesheet_templates' => 2,
            'certifications_vaccinations' => 3,
            'certifications_compliance' => 4,
            'timesheets_submit_tab' => 5,
            'timesheets_pending_tab' => 6,
            'timesheets_unsubmitted_tab' => 7,
            'timessheets_approved_tab' => 8,
            'rosters' => 9,
            'roster_templates' => 10
        ],
        self::FEATURE_ADMIN_SETTINGS => [
            'roles_and_permissions' => 1,
            'operational_structure' => 2,
            'payroll_information' => 3,
            'other_settings' => 4,
            'roles_and_permissions_roles_and_users' => 5,
            'roles_and_permissions_permissions' => 6,
            'operational_structure_divisions' => 7,
            'operational_structure_locations' => 8,
            'payroll_information_position_and_title' => 9,
            'payroll_information_employment_basis' => 10,
            'payroll_information_classifications_and_pay_ranges' => 11,
            'payroll_information_pay_rates_and_types' => 12,
            'payroll_information_allowances' => 13,
            'other_settings_email_activity' => 14
        ]
    ];

    // Actions
    const ACTION_VIEW = 1;
    const ACTION_CREATE = 2;
    const ACTION_UPDATE = 3;
    const ACTION_ARCHIVE = 4;
    const ACTION_DELETE = 5;
    const ACTION_SUBMIT = 6;
    const ACTION_APPROVE = 7;

    // Scopes
    const SCOPE_SELF = 1;
    const SCOPE_TEAMMATE = 2;
    const SCOPE_DIVISION = 3;
    const SCOPE_ALL = 4;

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withPivot('scope');
    }
}
