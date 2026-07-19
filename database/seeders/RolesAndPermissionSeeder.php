<?php

namespace Database\Seeders;

use App\Core\v1\Permission\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionSeeder extends Seeder
{

	const ACTION_MAP = [
		'view' => Permission::ACTION_VIEW,
		'create' => Permission::ACTION_CREATE,
		'update' => Permission::ACTION_UPDATE,
		'archive' => Permission::ACTION_ARCHIVE,
		'delete' => Permission::ACTION_DELETE,
		'submit' => Permission::ACTION_SUBMIT,
		'approve' => Permission::ACTION_APPROVE
	];
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0');
		DB::table('permission_role')->truncate();
		DB::table('permissions')->truncate();
		DB::table('roles')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1');
		// Roles
		$superUserRole      = Role::query()->insertGetId(array('name' => 'Super User'));
		$userRole           = Role::query()->insertGetId(array('name' => 'User'));
		$lineManagerRole    = Role::query()->insertGetId(array('name' => 'Line Manager'));
		$generalManagerRole = Role::query()->insertGetId(array('name' => 'General Manager'));
		$hrRole             = Role::query()->insertGetId(array('name' => 'HR'));
		$financeRole        = Role::query()->insertGetId(array('name' => 'Finance'));
		$ceoRole            = Role::query()->insertGetId(array('name' => 'CEO'));
		$boardRole          = Role::query()->insertGetId(array('name' => 'Board'));
		$applicantRole      = Role::query()->insertGetId(array('name' => 'Applicant'));
		$adminRole          = Role::query()->insertGetId(array('name' => 'Administrator '));

		// Permissions - Dashboard
		$dashboardView = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_DASHBOARD,
				'sub_feature' => null,
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'dashboard.view',
			)
		);
		// Permissions - People - Leave Management
		$peopleLeaveManagementView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'people.leave_management.view',
			)
		);
		$peopleLeaveManagementCreate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_CREATE,
				'ui_key'      => 'people.leave_management.create',
			)
		);
		$peopleLeaveManagementUpdate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_UPDATE,
				'ui_key'      => 'people.leave_management.update',
			)
		);
		$peopleLeaveManagementArchive = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_ARCHIVE,
				'ui_key'      => 'people.leave_management.archive',
			)
		);
		$peopleLeaveManagementDelete  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_DELETE,
				'ui_key'      => 'people.leave_management.delete',
			)
		);
		$peopleLeaveManagementSubmit  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_SUBMIT,
				'ui_key'      => 'people.leave_management.submit',
			)
		);
		$peopleLeaveManagementApprove = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
				'action'      => Permission::ACTION_APPROVE,
				'ui_key'      => 'people.leave_management.approve',
			)
		);
		// Permissions - People - Employee Dashboard
		$peopleEmployeeDashboardView = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'people.employee_dashboard.view',
			)
		);
		$peopleEmployeeDashboardCreate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_CREATE,
				'ui_key'      => 'people.employee_dashboard.create',
			)
		);
		$peopleEmployeeDashboardUpdate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_UPDATE,
				'ui_key'      => 'people.employee_dashboard.update',
			)
		);
		$peopleEmployeeDashboardArchive = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_ARCHIVE,
				'ui_key'      => 'people.employee_dashboard.archive',
			)
		);
		$peopleEmployeeDashboardDelete  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_DELETE,
				'ui_key'      => 'people.employee_dashboard.delete',
			)
		);
		$peopleEmployeeDashboardSubmit  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_SUBMIT,
				'ui_key'      => 'people.employee_dashboard.submit',
			)
		);
		$peopleEmployeeDashboardApprove = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_PEOPLE,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
				'action'      => Permission::ACTION_APPROVE,
				'ui_key'      => 'people.employee_dashboard.approve',
			)
		);
		// Permissions - Recruitment - Contract
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.view',
			)
		);
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.create',
			)
		);
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.update',
			)
		);
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.archive',
			)
		);
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.delete',
			)
		);
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.submit',
			)
		);
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_RECRUITMENT,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_RECRUITMENT]['contract'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'recruitment.contract.approve',
			)
		);
		// Permissions - Operational - Timesheets
		$operationTimesheetView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'operational.timesheets.view',
			)
		);
		$operationTimesheetCreate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_CREATE,
				'ui_key'      => 'operational.timesheets.create',
			)
		);
		$operationTimesheetUpdate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_UPDATE,
				'ui_key'      => 'operational.timesheets.view',
			)
		);
		$operationTimesheetArchive = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_ARCHIVE,
				'ui_key'      => 'operational.timesheets.archive',
			)
		);
		$operationTimesheetDelete  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_DELETE,
				'ui_key'      => 'operational.timesheets.delete',
			)
		);
		$operationTimesheetSubmit  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_SUBMIT,
				'ui_key'      => 'operational.timesheets.submit',
			)
		);
		$operationTimesheetApprove = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
				'action'      => Permission::ACTION_APPROVE,
				'ui_key'      => 'operational.timesheets.approve',
			)
		);

		// Permissions - Operational - Timesheet Templates
		$operationTimesheetTemplateView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'operational.timesheets.templates.view',
			)
		);
		$operationTimesheetTemplateCreate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
				'action'      => Permission::ACTION_CREATE,
				'ui_key'      => 'operational.timesheets.templates.view',
			)
		);
		$operationTimesheetTemplateUpdate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
				'action'      => Permission::ACTION_UPDATE,
				'ui_key'      => 'operational.timesheets.templates.update',
			)
		);
		$operationTimesheetTemplateArchive = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
				'action'      => Permission::ACTION_ARCHIVE,
				'ui_key'      => 'operational.timesheets.templates.archive',
			)
		);
		$operationTimesheetTemplateDelete  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
				'action'      => Permission::ACTION_DELETE,
				'ui_key'      => 'operational.timesheets.templates.delete',
			)
		);

		// Permissions - Operational - Certifications
		$operationCertificationVaccinationView = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['certifications_vaccinations'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'operational.certifications_vaccinations.view',
			)
		);
		$operationCertificationComplianceView  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_OPERATIONAL,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['certifications_compliance'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'operational.certifications_compliance.view',
			)
		);

		// Permissions - Admin Settings
		$adminSettingRolesAndPermissionsView    = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_ADMIN_SETTINGS,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions'],
				'action'      => Permission::ACTION_VIEW,
				'ui_key'      => 'admin_settings.roles_and_permissions.view',
			)
		);
		$adminSettingRolesAndPermissionsCreate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_ADMIN_SETTINGS,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions'],
				'action'      => Permission::ACTION_CREATE,
				'ui_key'      => 'admin_settings.roles_and_permissions.create',
			)
		);
		$adminSettingRolesAndPermissionsUpdate  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_ADMIN_SETTINGS,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions'],
				'action'      => Permission::ACTION_UPDATE,
				'ui_key'      => 'admin_settings.roles_and_permissions.update',
			)
		);
		$adminSettingRolesAndPermissionsArchive = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_ADMIN_SETTINGS,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions'],
				'action'      => Permission::ACTION_ARCHIVE,
				'ui_key'      => 'admin_settings.roles_and_permissions.archive',
			)
		);
		$adminSettingRolesAndPermissionsDelete  = Permission::query()->insertGetId(
			array(
				'feature'     => Permission::FEATURE_ADMIN_SETTINGS,
				'sub_feature' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions'],
				'action'      => Permission::ACTION_DELETE,
				'ui_key'      => 'admin_settings.roles_and_permissions.delete',
			)
		);

		// Timesheets - Submit a timesheet Tab
		$operationalSubmitATimesheetTab = $this->createPermissions(
			Permission::FEATURE_OPERATIONAL,
			Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_submit_tab'],
			'operational.timesheets_submit_tab',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);
		// Timesheets - Submit a timesheet Tab
		$operationalPendingTab = $this->createPermissions(
			Permission::FEATURE_OPERATIONAL,
			Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_pending_tab'],
			'operational.timesheets_pending_tab',
			['view', 'update', 'submit', 'approve']
		);
		// Timesheets - Submit a timesheet Tab
		$operationalUnsubmittedTab = $this->createPermissions(
			Permission::FEATURE_OPERATIONAL,
			Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_unsubmitted_tab'],
			'operational.timesheets_unsubmitted_tab',
			['view', 'update', 'submit', 'delete', 'approve']
		);
		// Timesheets - Approve a timesheet Tab
		$operationalApprovedTab = $this->createPermissions(
			Permission::FEATURE_OPERATIONAL,
			Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timessheets_approved_tab'],
			'operational.timessheets_approved_tab',
			['view', 'update', 'delete', 'approve']
		);

		// Leave Management - Leave Request Tab
		$peopleLeaveManagementLeaveRequestTab = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_request'],
			'people.leave_management_leave_request',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);
		// Leave Management - Leave Request Tab
		$peopleLeaveManagementLeaveCalendarTab = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_calendar'],
			'people.leave_management_leave_calendar',
			['view']
		);
		// Leave Management - Leave Request Tab
		$peopleLeaveManagementApplyForLeaveWizard = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_apply_wizard'],
			'people.leave_management_apply_wizard',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);
		// Operational - Rosters
		$operationalRosters = $this->createPermissions(
			Permission::FEATURE_OPERATIONAL,
			Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['rosters'],
			'operational.rosters',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		// Operational - Roster Templates
		$operationalRosters = $this->createPermissions(
			Permission::FEATURE_OPERATIONAL,
			Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['roster_templates'],
			'operational.roster_templates',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		// Leave Management - Leave Request Tab
		$peopleLeaveManagementLeaveRequestTab = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_request'],
			'people.leave_management_leave_request',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);
		// Leave Management - Leave Request Tab
		$peopleLeaveManagementLeaveCalendarTab = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_calendar'],
			'people.leave_management_leave_calendar',
			['view']
		);
		// Leave Management - Leave Request Tab
		$peopleLeaveManagementApplyForLeaveWizard = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_apply_wizard'],
			'people.leave_management_apply_wizard',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeSection = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard_employee_section'],
			'people.employee_dashboard_employee_section',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeContactSection = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_profile_contact_section'],
			'people.employee_profile_contact_section',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeProfilePersonalDetail = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_profile_personal_detail'],
			'people.employee_profile_personal_detail',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeInfoEmergencyContact = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_info_emergency_contact'],
			'people.employee_info_emergency_contact',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeInfoMedical = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_info_medical'],
			'people.employee_info_medical',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeCertificationVaccination = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_certification_vaccinations'],
			'people.employee_certification_vaccinations',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeCertificationCompliance = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_certification_compliance'],
			'people.employee_certification_compliance',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeePayBankInformation = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_pay_bank_information'],
			'people.employee_pay_bank_information',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeePayTFN = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_pay_tfn'],
			'people.employee_pay_tfn',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeePaySuperInfo = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_super_info'],
			'people.employee_super_info',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeLeave = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_leave'],
			'people.employee_leave',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeContract = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_contract'],
			'people.employee_contract',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeNotes = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_notes'],
			'people.employee_notes',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$peopleEmployeeActivityLog = $this->createPermissions(
			Permission::FEATURE_PEOPLE,
			Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_activity_logs'],
			'people.employee_activity_logs',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingOperationalStructure = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['operational_structure'],
			'admin_settings.operational_structure',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingPayrollInformation = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information'],
			'admin_settings.payroll_information',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingOtherSettings = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['other_settings'],
			'admin_settings.other_settings',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingRolesAndUsers = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions_roles_and_users'],
			'admin_settings.roles_and_permissions_roles_and_users',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingPermissions = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions_permissions'],
			'admin_settings.roles_and_permissions_permissions',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingDivisions = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['operational_structure_divisions'],
			'admin_settings.operational_structure_divisions',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingLocations = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['operational_structure_locations'],
			'admin_settings.operational_structure_locations',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingPositionAndTitle = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_position_and_title'],
			'admin_settings.payroll_information_position_and_title',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingEmploymentBasis = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_employment_basis'],
			'admin_settings.payroll_information_employment_basis',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingClassificationsAndPayRanges = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_classifications_and_pay_ranges'],
			'admin_settings.payroll_information_classifications_and_pay_ranges',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingPayRatesAndTypes = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_pay_rates_and_types'],
			'admin_settings.payroll_information_pay_rates_and_types',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingAllowances = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_allowances'],
			'admin_settings.payroll_information_allowances',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$adminSettingEmailActivity = $this->createPermissions(
			Permission::FEATURE_ADMIN_SETTINGS,
			Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['other_settings_email_activity'],
			'admin_settings.other_settings_email_activity',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		$resources = $this->createPermissions(
			Permission::FEATURE_RESOURCES,
			null,
			'resources',
			['view', 'create', 'update', 'delete', 'submit', 'approve']
		);

		/*
		 * Roles & Permissions
		 *
		 */

		// Add dashboard view permission to all roles
		$this->addPermissionToRole($userRole, $dashboardView);
		$this->addPermissionToRole($lineManagerRole, $dashboardView);
		$this->addPermissionToRole($generalManagerRole, $dashboardView);
		$this->addPermissionToRole($hrRole, $dashboardView);
		$this->addPermissionToRole($financeRole, $dashboardView);
		$this->addPermissionToRole($ceoRole, $dashboardView);
		$this->addPermissionToRole($boardRole, $dashboardView);
		$this->addPermissionToRole($applicantRole, $dashboardView);
		$this->addPermissionToRole($adminRole, $dashboardView);
		// Add employee dashboard permissions to - User
		$this->addPermissionToRole($userRole, $peopleEmployeeDashboardView);
		// Add employee dashboard permissions to - Line Manager
		$this->addPermissionToRole($lineManagerRole, $peopleEmployeeDashboardView);
		// Add employee dashboard permissions to - General Manager
		$this->addPermissionToRole($generalManagerRole, $peopleEmployeeDashboardView, Permission::SCOPE_ALL);
		// Add employee dashboard permissions to - HR
		$this->addPermissionToRole($hrRole, $peopleEmployeeDashboardView, Permission::SCOPE_ALL);
		// Add employee dashboard permissions to - Finance
		$this->addPermissionToRole($financeRole, $peopleEmployeeDashboardView);
		// Add employee dashboard permissions to - CEO
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardView, Permission::SCOPE_ALL);
		// Add employee dashboard permissions to - Admin
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardView, Permission::SCOPE_ALL);
		// Add leave management permissions to - Line Manager role
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementView, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementCreate, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementUpdate, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementArchive, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementDelete, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementSubmit, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementApprove, Permission::SCOPE_TEAMMATE);
		// Add leave management permissions to - General Manager role
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementView, Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementCreate, Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementUpdate, Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementArchive, Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementDelete, Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementSubmit, Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementApprove, Permission::SCOPE_DIVISION);
		// Add leave management permissions to - HR role
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementApprove, Permission::SCOPE_ALL);
		// Add leave management permissions to - Finance role
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementSubmit, Permission::SCOPE_ALL);
		// Add leave management permissions to - CEO role
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementApprove, Permission::SCOPE_ALL);
		// Add leave management permissions to - Admin role
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementApprove, Permission::SCOPE_ALL);
		// No Leave management permissions for BOARD and APPLICANT
		// Add Timesheets Permissions - Line Manager Role
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetView);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetCreate);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetUpdate);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetArchive);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetDelete);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetSubmit);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetApprove, Permission::SCOPE_TEAMMATE);
		// Timesheet Template Permissions
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetTemplateView);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetTemplateCreate);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetTemplateUpdate);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetTemplateArchive);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetTemplateDelete);
		// Add Timesheets Permissions - General  Manager Role
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetView);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetCreate);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetUpdate);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetArchive);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetDelete);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetSubmit);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetApprove, Permission::SCOPE_DIVISION);
		// Timesheet Template Permissions
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetTemplateView);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetTemplateCreate);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetTemplateUpdate);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetTemplateArchive);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetTemplateDelete);
		// Add Timesheets Permissions - HR Role
		$this->addPermissionToRole($hrRole, $operationTimesheetView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetApprove, Permission::SCOPE_ALL);
		// Timesheet Template Permissions
		$this->addPermissionToRole($hrRole, $operationTimesheetTemplateView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetTemplateCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetTemplateUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetTemplateArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetTemplateDelete, Permission::SCOPE_ALL);
		// Add Timesheets Permissions - Finance Role
		$this->addPermissionToRole($financeRole, $operationTimesheetView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationTimesheetCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationTimesheetUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationTimesheetArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationTimesheetDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationTimesheetSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationTimesheetApprove, Permission::SCOPE_ALL);
		// Add Timesheets Permissions - CEO Role
		$this->addPermissionToRole($ceoRole, $operationTimesheetView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetApprove, Permission::SCOPE_ALL);
		// Timesheet Template Permissions
		$this->addPermissionToRole($ceoRole, $operationTimesheetTemplateView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetTemplateCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetTemplateUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetTemplateArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationTimesheetTemplateDelete, Permission::SCOPE_ALL);
		// Add Timesheets Permissions - Admin Role
		$this->addPermissionToRole($adminRole, $operationTimesheetView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetApprove, Permission::SCOPE_ALL);
		// Timesheet Template Permissions
		$this->addPermissionToRole($adminRole, $operationTimesheetTemplateView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetTemplateCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetTemplateUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetTemplateArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetTemplateDelete, Permission::SCOPE_ALL);
		// Certification Compliance Permissions - User
		$this->addPermissionToRole($userRole, $operationCertificationComplianceView);
		// Certification Compliance Permissions - Line Manager
		$this->addPermissionToRole($lineManagerRole, $operationCertificationComplianceView, Permission::SCOPE_TEAMMATE);
		// Certification Compliance Permissions - General Manager
		$this->addPermissionToRole($generalManagerRole, $operationCertificationComplianceView, Permission::SCOPE_TEAMMATE);
		// Certification Compliance Permissions - HR
		$this->addPermissionToRole($hrRole, $operationCertificationComplianceView, Permission::SCOPE_ALL);
		// Certification Compliance Permissions - CEO
		$this->addPermissionToRole($ceoRole, $operationCertificationComplianceView, Permission::SCOPE_ALL);
		// Certification Compliance Permissions - CEO
		$this->addPermissionToRole($ceoRole, $operationCertificationComplianceView, Permission::SCOPE_ALL);
		// Certification Compliance Permissions - Board
		$this->addPermissionToRole($boardRole, $operationCertificationComplianceView);
		// Certification Compliance Permissions - Applicant
		$this->addPermissionToRole($applicantRole, $operationCertificationComplianceView);
		// Certification Compliance Permissions - Admin
		$this->addPermissionToRole($applicantRole, $adminRole, Permission::SCOPE_ALL);
		// Certification Vaccinations Permissions - User
		$this->addPermissionToRole($userRole, $operationCertificationVaccinationView);
		// Certification Vaccinations Permissions - Line Manager
		$this->addPermissionToRole($lineManagerRole, $operationCertificationVaccinationView, Permission::SCOPE_TEAMMATE);
		// Certification Vaccinations Permissions - General Manager
		$this->addPermissionToRole($generalManagerRole, $operationCertificationVaccinationView, Permission::SCOPE_TEAMMATE);
		// Certification Vaccinations Permissions - HR
		$this->addPermissionToRole($hrRole, $operationCertificationVaccinationView, Permission::SCOPE_ALL);
		// Certification Vaccinations Permissions - CEO
		$this->addPermissionToRole($ceoRole, $operationCertificationVaccinationView, Permission::SCOPE_ALL);
		// Certification Vaccinations Permissions - CEO
		$this->addPermissionToRole($ceoRole, $operationCertificationVaccinationView, Permission::SCOPE_ALL);
		// Certification Vaccinations Permissions - Board
		$this->addPermissionToRole($boardRole, $operationCertificationVaccinationView);
		// Certification Vaccinations Permissions - Applicant
		$this->addPermissionToRole($applicantRole, $operationCertificationVaccinationView);
		// Certification Vaccinations Permissions - Admin
		$this->addPermissionToRole($adminRole, $operationCertificationVaccinationView);
		// Add admin settings (roles and permissions) permissions to - HR
		$this->addPermissionToRole($hrRole, $adminSettingRolesAndPermissionsView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $adminSettingRolesAndPermissionsCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $adminSettingRolesAndPermissionsUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $adminSettingRolesAndPermissionsArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $adminSettingRolesAndPermissionsDelete, Permission::SCOPE_ALL);
		// Add admin settings (roles and permissions) permissions to - CEO
		$this->addPermissionToRole($ceoRole, $adminSettingRolesAndPermissionsView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $adminSettingRolesAndPermissionsCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $adminSettingRolesAndPermissionsUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $adminSettingRolesAndPermissionsArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $adminSettingRolesAndPermissionsDelete, Permission::SCOPE_ALL);
		// Add admin settings (roles and permissions) permissions to - Admin
		$this->addPermissionToRole($adminRole, $adminSettingRolesAndPermissionsView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $adminSettingRolesAndPermissionsCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $adminSettingRolesAndPermissionsUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $adminSettingRolesAndPermissionsArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $adminSettingRolesAndPermissionsDelete, Permission::SCOPE_ALL);
		// People Permission
		// Admin Role
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleEmployeeDashboardApprove, Permission::SCOPE_ALL);
		// CEO
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardView);
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardCreate);
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardUpdate);
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardArchive);
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardDelete);
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardSubmit);
		$this->addPermissionToRole($ceoRole, $peopleEmployeeDashboardApprove);
		// Finance
		$this->addPermissionToRole($financeRole, $peopleEmployeeDashboardView);
		$this->addPermissionToRole($financeRole, $peopleEmployeeDashboardSubmit);
		// HR
		$this->addPermissionToRole($hrRole, $peopleEmployeeDashboardView);
		$this->addPermissionToRole($hrRole, $peopleEmployeeDashboardUpdate);
		$this->addPermissionToRole($hrRole, $peopleEmployeeDashboardSubmit);
		// General Manager
		$this->addPermissionToRole($generalManagerRole, $peopleEmployeeDashboardView);
		$this->addPermissionToRole($generalManagerRole, $peopleEmployeeDashboardSubmit);
		// Line Manager
		$this->addPermissionToRole($lineManagerRole, $peopleEmployeeDashboardView);
		$this->addPermissionToRole($lineManagerRole, $peopleEmployeeDashboardSubmit);
		// User Role
		$this->addPermissionToRole($userRole, $peopleEmployeeDashboardView);
		// Board Role
		$this->addPermissionToRole($boardRole, $peopleEmployeeDashboardView);
		// Applicant Role
		$this->addPermissionToRole($applicantRole, $peopleEmployeeDashboardView);
		// Operational Permission
		// Admin Role
		$this->addPermissionToRole($adminRole, $operationTimesheetView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationTimesheetApprove, Permission::SCOPE_ALL);
		// CEO Role
		$this->addPermissionToRole($ceoRole, $operationTimesheetView);
		$this->addPermissionToRole($ceoRole, $operationTimesheetCreate);
		$this->addPermissionToRole($ceoRole, $operationTimesheetUpdate);
		$this->addPermissionToRole($ceoRole, $operationTimesheetArchive);
		$this->addPermissionToRole($ceoRole, $operationTimesheetDelete);
		$this->addPermissionToRole($ceoRole, $operationTimesheetSubmit);
		$this->addPermissionToRole($ceoRole, $operationTimesheetApprove);
		// Finance Role
		$this->addPermissionToRole($financeRole, $operationTimesheetView);
		$this->addPermissionToRole($financeRole, $operationTimesheetCreate);
		$this->addPermissionToRole($financeRole, $operationTimesheetUpdate);
		$this->addPermissionToRole($financeRole, $operationTimesheetArchive);
		$this->addPermissionToRole($financeRole, $operationTimesheetDelete);
		$this->addPermissionToRole($financeRole, $operationTimesheetSubmit);
		$this->addPermissionToRole($financeRole, $operationTimesheetApprove);
		// HR Role
		$this->addPermissionToRole($hrRole, $operationTimesheetView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetCreate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetUpdate, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetArchive, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetDelete, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetSubmit, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationTimesheetApprove, Permission::SCOPE_ALL);
		// General Manager Role
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetView, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetCreate, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetUpdate, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetSubmit, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($generalManagerRole, $operationTimesheetApprove, Permission::SCOPE_TEAMMATE);
		// Line Manager Role
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetView, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetCreate, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetUpdate, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetSubmit, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $operationTimesheetApprove, Permission::SCOPE_TEAMMATE);
		// User Role
		$this->addPermissionToRole($userRole, $operationTimesheetView);
		$this->addPermissionToRole($userRole, $operationTimesheetCreate);
		$this->addPermissionToRole($userRole, $operationTimesheetUpdate);
		$this->addPermissionToRole($userRole, $operationTimesheetSubmit);
		// Certification Permission
		// Admin Role
		$this->addPermissionToRole($adminRole, $operationCertificationVaccinationView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationCertificationComplianceView, Permission::SCOPE_ALL);
		// CEO Role
		$this->addPermissionToRole($ceoRole, $operationCertificationVaccinationView);
		$this->addPermissionToRole($ceoRole, $operationCertificationComplianceView);
		// HR Role
		$this->addPermissionToRole($hrRole, $operationCertificationVaccinationView, Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationCertificationComplianceView, Permission::SCOPE_ALL);
		// General Manager Role
		$this->addPermissionToRole($generalManagerRole, $operationCertificationVaccinationView, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($generalManagerRole, $operationCertificationComplianceView, Permission::SCOPE_TEAMMATE);
		// Line Manager Role
		$this->addPermissionToRole($lineManagerRole, $operationCertificationVaccinationView, Permission::SCOPE_TEAMMATE);
		$this->addPermissionToRole($lineManagerRole, $operationCertificationComplianceView, Permission::SCOPE_TEAMMATE);

		// User - Submit a Timesheet
		$this->addPermissionToRole($userRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($userRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($userRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($userRole, $operationalSubmitATimesheetTab['submit']);
		// User - Pending Tab
		$this->addPermissionToRole($userRole, $operationalPendingTab['view']);
		// User - Unsubmitted Tab
		$this->addPermissionToRole($userRole, $operationalUnsubmittedTab['view']);
		// User - Approved Tab
		$this->addPermissionToRole($userRole, $operationalApprovedTab['view']);

		// Line Manager - Submit a Timesheet
		$this->addPermissionToRole($lineManagerRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($lineManagerRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($lineManagerRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($lineManagerRole, $operationalSubmitATimesheetTab['submit']);
		// Line Manager - Pending Tab
		$this->addPermissionToRole($lineManagerRole, $operationalPendingTab['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $operationalPendingTab['approve'], Permission::SCOPE_DIVISION);
		// Line Manager - Unsubmitted Tab
		$this->addPermissionToRole($lineManagerRole, $operationalUnsubmittedTab['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $operationalUnsubmittedTab['approve'], Permission::SCOPE_DIVISION);
		// Line Manager - Approved Tab
		$this->addPermissionToRole($lineManagerRole, $operationalApprovedTab['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $operationalApprovedTab['approve'], Permission::SCOPE_DIVISION);

		// General Manager - Submit a Timesheet
		$this->addPermissionToRole($generalManagerRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($generalManagerRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($generalManagerRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($generalManagerRole, $operationalSubmitATimesheetTab['submit']);
		// General Manager - Pending Tab
		$this->addPermissionToRole($generalManagerRole, $operationalPendingTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $operationalPendingTab['approve'], Permission::SCOPE_ALL);
		// General Manager - Unsubmitted Tab
		$this->addPermissionToRole($generalManagerRole, $operationalUnsubmittedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $operationalUnsubmittedTab['approve'], Permission::SCOPE_ALL);
		// General Manager - Approved Tab
		$this->addPermissionToRole($generalManagerRole, $operationalApprovedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $operationalApprovedTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $operationalApprovedTab['approve'], Permission::SCOPE_ALL);

		// HR - Submit a Timesheet
		$this->addPermissionToRole($hrRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($hrRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($hrRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($hrRole, $operationalSubmitATimesheetTab['submit']);
		// HR - Pending Tab
		$this->addPermissionToRole($hrRole, $operationalPendingTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationalPendingTab['approve'], Permission::SCOPE_ALL);
		// HR - Unsubmitted Tab
		$this->addPermissionToRole($hrRole, $operationalUnsubmittedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationalUnsubmittedTab['approve'], Permission::SCOPE_ALL);
		// HR - Approved Tab
		$this->addPermissionToRole($hrRole, $operationalApprovedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationalApprovedTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $operationalApprovedTab['approve'], Permission::SCOPE_ALL);

		// Finance - Submit a Timesheet
		$this->addPermissionToRole($financeRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($financeRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($financeRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($financeRole, $operationalSubmitATimesheetTab['submit']);
		// Finance - Pending Tab
		$this->addPermissionToRole($financeRole, $operationalPendingTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationalPendingTab['approve'], Permission::SCOPE_ALL);
		// Finance - Unsubmitted Tab
		$this->addPermissionToRole($financeRole, $operationalUnsubmittedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationalUnsubmittedTab['approve'], Permission::SCOPE_ALL);
		// Finance - Approved Tab
		$this->addPermissionToRole($financeRole, $operationalApprovedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationalApprovedTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($financeRole, $operationalApprovedTab['approve'], Permission::SCOPE_ALL);

		// CEO - Submit a Timesheet
		$this->addPermissionToRole($ceoRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($ceoRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($ceoRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($ceoRole, $operationalSubmitATimesheetTab['submit']);
		// CEO - Pending Tab
		$this->addPermissionToRole($ceoRole, $operationalPendingTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationalPendingTab['approve'], Permission::SCOPE_ALL);
		// CEO - Unsubmitted Tab
		$this->addPermissionToRole($ceoRole, $operationalUnsubmittedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationalUnsubmittedTab['approve'], Permission::SCOPE_ALL);
		// CEO - Approved Tab
		$this->addPermissionToRole($ceoRole, $operationalApprovedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationalApprovedTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $operationalApprovedTab['approve'], Permission::SCOPE_ALL);

		// Admin - Submit a Timesheet
		$this->addPermissionToRole($adminRole, $operationalSubmitATimesheetTab['view']);
		$this->addPermissionToRole($adminRole, $operationalSubmitATimesheetTab['create']);
		$this->addPermissionToRole($adminRole, $operationalSubmitATimesheetTab['update']);
		$this->addPermissionToRole($adminRole, $operationalSubmitATimesheetTab['submit']);
		// Admin - Pending Tab
		$this->addPermissionToRole($adminRole, $operationalPendingTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationalPendingTab['approve'], Permission::SCOPE_ALL);
		// Admin - Unsubmitted Tab
		$this->addPermissionToRole($adminRole, $operationalUnsubmittedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationalUnsubmittedTab['approve'], Permission::SCOPE_ALL);
		// Admin - Approved Tab
		$this->addPermissionToRole($adminRole, $operationalApprovedTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationalApprovedTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $operationalApprovedTab['approve'], Permission::SCOPE_ALL);

		// User - Leave Management - Leave Request
		$this->addPermissionToRole($userRole, $peopleLeaveManagementLeaveRequestTab['view']);
		$this->addPermissionToRole($userRole, $peopleLeaveManagementLeaveRequestTab['create']);
		$this->addPermissionToRole($userRole, $peopleLeaveManagementLeaveRequestTab['update']);
		$this->addPermissionToRole($userRole, $peopleLeaveManagementLeaveRequestTab['submit']);
		// User - Leave Management - Leave Calendar
		$this->addPermissionToRole($userRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_DIVISION);
		// User - Leave Management - Apply Wizard
		$this->addPermissionToRole($userRole, $peopleLeaveManagementApplyForLeaveWizard['view']);
		$this->addPermissionToRole($userRole, $peopleLeaveManagementApplyForLeaveWizard['create']);
		$this->addPermissionToRole($userRole, $peopleLeaveManagementApplyForLeaveWizard['update']);
		$this->addPermissionToRole($userRole, $peopleLeaveManagementApplyForLeaveWizard['submit']);

		// Line Manager - Leave Management - Leave Request
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementLeaveRequestTab['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementLeaveRequestTab['create'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementLeaveRequestTab['update'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementLeaveRequestTab['submit'], Permission::SCOPE_DIVISION);
		// Line Manager - Leave Management - Leave Calendar
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_DIVISION);
		// Line Manager - Leave Management - Apply Wizard
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementApplyForLeaveWizard['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementApplyForLeaveWizard['create'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementApplyForLeaveWizard['update'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($lineManagerRole, $peopleLeaveManagementApplyForLeaveWizard['submit'], Permission::SCOPE_DIVISION);

		// General Manager - Leave Management - Leave Request
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementLeaveRequestTab['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementLeaveRequestTab['create'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementLeaveRequestTab['update'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementLeaveRequestTab['submit'], Permission::SCOPE_DIVISION);
		// General Manager - Leave Management - Leave Calendar
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_DIVISION);
		// General Manager - Leave Management - Apply Wizard
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementApplyForLeaveWizard['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementApplyForLeaveWizard['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementApplyForLeaveWizard['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($generalManagerRole, $peopleLeaveManagementApplyForLeaveWizard['submit'], Permission::SCOPE_ALL);

		// HR - Leave Management - Leave Request
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementLeaveRequestTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementLeaveRequestTab['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementLeaveRequestTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementLeaveRequestTab['submit'], Permission::SCOPE_ALL);
		// HR - Leave Management - Leave Calendar
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_ALL);
		// HR - Leave Management - Apply Wizard
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementApplyForLeaveWizard['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementApplyForLeaveWizard['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementApplyForLeaveWizard['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($hrRole, $peopleLeaveManagementApplyForLeaveWizard['submit'], Permission::SCOPE_ALL);

		// Finance - Leave Management - Leave Request
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementLeaveRequestTab['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementLeaveRequestTab['create'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementLeaveRequestTab['update'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementLeaveRequestTab['submit'], Permission::SCOPE_DIVISION);
		// Finance - Leave Management - Leave Calendar
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_DIVISION);
		// Finance - Leave Management - Apply Wizard
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementApplyForLeaveWizard['view'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementApplyForLeaveWizard['create'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementApplyForLeaveWizard['update'], Permission::SCOPE_DIVISION);
		$this->addPermissionToRole($financeRole, $peopleLeaveManagementApplyForLeaveWizard['submit'], Permission::SCOPE_DIVISION);

		// CEO - Leave Management - Leave Request
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementLeaveRequestTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementLeaveRequestTab['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementLeaveRequestTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementLeaveRequestTab['submit'], Permission::SCOPE_ALL);
		// CEO - Leave Management - Leave Calendar
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_ALL);
		// CEO - Leave Management - Apply Wizard
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementApplyForLeaveWizard['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementApplyForLeaveWizard['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementApplyForLeaveWizard['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($ceoRole, $peopleLeaveManagementApplyForLeaveWizard['submit'], Permission::SCOPE_ALL);

		// Admin - Leave Management - Leave Request
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementLeaveRequestTab['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementLeaveRequestTab['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementLeaveRequestTab['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementLeaveRequestTab['submit'], Permission::SCOPE_ALL);
		// Admin - Leave Management - Leave Calendar
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementLeaveCalendarTab['view'], Permission::SCOPE_ALL);
		// Admin - Leave Management - Apply Wizard
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementApplyForLeaveWizard['view'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementApplyForLeaveWizard['create'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementApplyForLeaveWizard['update'], Permission::SCOPE_ALL);
		$this->addPermissionToRole($adminRole, $peopleLeaveManagementApplyForLeaveWizard['submit'], Permission::SCOPE_ALL);

		// Board - Leave Management - Leave Request
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementLeaveRequestTab['view']);
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementLeaveRequestTab['create']);
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementLeaveRequestTab['update']);
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementLeaveRequestTab['submit']);
		// Board - Leave Management - Leave Calendar
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementLeaveCalendarTab['view']);
		// Board - Leave Management - Apply Wizard
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementApplyForLeaveWizard['view']);
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementApplyForLeaveWizard['create']);
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementApplyForLeaveWizard['update']);
		$this->addPermissionToRole($boardRole, $peopleLeaveManagementApplyForLeaveWizard['submit']);
	}

	public function createPermissions(int $featureId, int|null $subFeatureId, string $uiKeyBase, array $actions)
	{
		$permissions = [];

		foreach ($actions as $action) {
			$permissions[$action] = Permission::query()->insertGetId(
				array(
					'feature'     => $featureId,
					'sub_feature' => $subFeatureId,
					'action'      => self::ACTION_MAP[$action],
					'ui_key'      => "{$uiKeyBase}.{$action}",
				)
			);
		}

		return $permissions;
	}

	protected function addPermissionToRole(int $roleId, int $permissionId, int $scope = Permission::SCOPE_SELF): void
	{
		DB::table('permission_role')->insert(
			array(
				'role_id'       => $roleId,
				'permission_id' => $permissionId,
				'scope'         => $scope,
			)
		);
	}
}
