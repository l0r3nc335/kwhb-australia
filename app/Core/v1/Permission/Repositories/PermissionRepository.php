<?php

namespace App\Core\v1\Permission\Repositories;

use App\Core\v1\Permission\Models\Permission;
use App\Repositories\ModelRepository;
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends ModelRepository
{
    public function model(): Model
    {
        return new Permission;
    }

    public function permissionSettings(): array
    {
        return [
            'title' => 'Screens',
            'items' => [
                [
                    'name' => 'Dashboard',
                    'feature_id' => Permission::FEATURE_DASHBOARD,
                    'sub_feature_id' => null,
                    'created_at' => '15/09/24, 12:00',
                    'updated_at' => '15/09/24, 12:00',
                    'actions' => [
                        Permission::ACTION_VIEW
                    ]
                ],
                [
                    'name' => 'People',
                    'feature_id' => Permission::FEATURE_PEOPLE,
                    'sub_feature_id' => null,
                    'created_at' => '15/09/24, 12:00',
                    'updated_at' => '15/09/24, 12:00',
                    'settings' => [
                        'title' => 'Sub Menus',
                        'items' => [
                            [
                                'name' => 'Employees',
                                'description' => 'Employee management for updating info, super annuation, tax, bank details.',
                                'feature_id' => Permission::FEATURE_PEOPLE,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Screens',
                                    'items' => [
                                        [
                                            'name' => 'Dashboard',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'All',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard'],
                                                    ],
                                                    [
                                                        'name' => 'Employee Section',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_dashboard_employee_section'],
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Profiles',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => null,
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'Contact Section',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_profile_contact_section'],
                                                    ],
                                                    [
                                                        'name' => 'Personal Details',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_profile_personal_detail'],
                                                    ],
                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Info',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => null,
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'Emergency Contact',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_info_emergency_contact'],
                                                    ],
                                                    [
                                                        'name' => 'Medical',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_info_medical'],
                                                    ],

                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Certifications',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => null,
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'Vaccinations',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_certification_vaccinations'],
                                                    ],
                                                    [
                                                        'name' => 'Compliance',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_certification_compliance'],
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Pay',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => null,
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'Bank Information',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_pay_bank_information'],
                                                    ],
                                                    [
                                                        'name' => 'TFN',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_pay_tfn'],
                                                    ],
                                                    [
                                                        'name' => 'Super Info',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_super_info'],
                                                    ],
                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Leave',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => null,
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'Apply For Leave',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_leave'],
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Contract',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => null,
                                            'settings' => [
                                                'title' => 'Sections',
                                                'items' => [
                                                    [
                                                        'name' => 'All',
                                                        'feature_id' => Permission::FEATURE_PEOPLE,
                                                        'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_contract'],
                                                    ]
                                                ]
                                            ]
                                        ],
                                        [
                                            'name' => 'Notes',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_notes'],
                                        ],
                                        [
                                            'name' => 'Activity Logs',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['employee_activity_logs'],
                                        ],
                                    ],

                                ]
                            ],
                            [
                                'name' => 'Leave Management',
                                'description' => 'Management for employee leaves; apply, approve, reject etc.',
                                'feature_id' => Permission::FEATURE_PEOPLE,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Screens',
                                    'items' => [
                                        [
                                            'name' => 'Leave Management',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management'],
                                            'actions' => [
                                                Permission::ACTION_VIEW
                                            ]
                                        ],
                                        [
                                            'name' => 'Leave Request',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_request'],
                                        ],
                                        [
                                            'name' => 'Leave Calendar',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_leave_calendar'],
                                        ],
                                        [
                                            'name' => 'Apply for Leave Wizard',
                                            'feature_id' => Permission::FEATURE_PEOPLE,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_PEOPLE]['leave_management_apply_wizard'],
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'Operational',
                    'feature_id' => Permission::FEATURE_OPERATIONAL,
                    'sub_feature_id' => null,
                    'created_at' => '15/09/24, 12:00',
                    'updated_at' => '15/09/24, 12:00',
                    'settings' => [
                        'title' => 'Sub menu',
                        'items' => [
                            [
                                'name' => 'Timesheets',
                                'feature_id' => Permission::FEATURE_OPERATIONAL,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Functions',
                                    'items' => [
                                        [
                                            'name' => 'Timesheets',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets'],
                                        ],
                                        [
                                            'name' => 'Submit Timesheet Tab',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_submit_tab'],
                                        ],
                                        [
                                            'name' => 'Pending Approval Tab',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_pending_tab'],
                                        ],
                                        [
                                            'name' => 'Unsubmitted Tab',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheets_unsubmitted_tab'],
                                        ],
                                        [
                                            'name' => 'Approved Tab',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timessheets_approved_tab'],
                                        ],
                                        [
                                            'name' => 'Templates',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['timesheet_templates'],
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'name' => 'Rosters',
                                'feature_id' => Permission::FEATURE_OPERATIONAL,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Sections',
                                    'items' => [
                                        [
                                            'name' => 'Rosters',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['rosters'],
                                        ],
                                        [
                                            'name' => 'Roster Templates',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['roster_templates'],
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'name' => 'Certifications',
                                'feature_id' => Permission::FEATURE_OPERATIONAL,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Sections',
                                    'items' => [
                                        [
                                            'name' => 'Vaccinations',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['certifications_vaccinations'],
                                        ],
                                        [
                                            'name' => 'Compliance',
                                            'feature_id' => Permission::FEATURE_OPERATIONAL,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_OPERATIONAL]['certifications_compliance'],
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ]
                ],
                [
                    'name' => 'Admin Settings',
                    'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                    'sub_feature_id' => null,
                    'created_at' => '15/09/24, 12:00',
                    'updated_at' => '15/09/24, 12:00',
                    'settings' => [
                        'title' => 'Sub Menus',
                        'items' => [
                            [
                                'name' => 'Roles & Permissions',
                                'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Screens',
                                    'items' => [
                                        [
                                            'name' => 'Roles & Users',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions_roles_and_users']
                                        ],
                                        [
                                            'name' => 'Permissions',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['roles_and_permissions_permissions']
                                        ],
                                    ]
                                ]
                            ],
                            [
                                'name' => 'Organisational Structure',
                                'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Screens',
                                    'items' => [
                                        [
                                            'name' => 'Divisions',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['operational_structure_divisions']
                                        ],
                                        [
                                            'name' => 'Locations',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['operational_structure_locations']
                                        ],
                                    ]
                                ]
                            ],
                            [
                                'name' => 'Payroll Information',
                                'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Screens',
                                    'items' => [
                                        [
                                            'name' => 'Position & Titles',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_position_and_title']
                                        ],
                                        [
                                            'name' => 'Employment Basis',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_employment_basis']
                                        ],
                                        [
                                            'name' => 'Classifications & Pay Ranges',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_classifications_and_pay_ranges']
                                        ],
                                        [
                                            'name' => 'Pay Rates & Types',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_pay_rates_and_types']
                                        ],
                                        [
                                            'name' => 'Allowances',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['payroll_information_allowances']
                                        ],
                                    ]
                                ]
                            ],
                            [
                                'name' => 'Other Settings',
                                'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                'sub_feature_id' => null,
                                'settings' => [
                                    'title' => 'Screens',
                                    'items' => [
                                        [
                                            'name' => 'Email Activity',
                                            'feature_id' => Permission::FEATURE_ADMIN_SETTINGS,
                                            'sub_feature_id' => Permission::SUB_FEATURES[Permission::FEATURE_ADMIN_SETTINGS]['other_settings_email_activity']
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ]
                ],
            ]
        ];
    }

    public function getActions(): array
    {
        return [
            [
                'id' => Permission::ACTION_VIEW,
                'label' => 'View'
            ],
            [
                'id' => Permission::ACTION_CREATE,
                'label' => 'Create'
            ],
            [
                'id' => Permission::ACTION_UPDATE,
                'label' => 'Update'
            ],
            [
                'id' => Permission::ACTION_ARCHIVE,
                'label' => 'Update'
            ],
            [
                'id' => Permission::ACTION_DELETE,
                'label' => 'Delete'
            ],
            [
                'id' => Permission::ACTION_SUBMIT,
                'label' => 'Submit'
            ],
            [
                'id' => Permission::ACTION_APPROVE,
                'label' => 'Approve'
            ],
        ];
    }

    public function getScopes(): array
    {
        return [
            [
                'id' => Permission::SCOPE_SELF,
                'label' => 'Only Me'
            ],
            [
                'id' => Permission::SCOPE_DIVISION,
                'label' => 'Division',
            ],
            [
                'id' => Permission::SCOPE_TEAMMATE,
                'label' => 'Teammate'
            ],
            [
                'id' => Permission::SCOPE_ALL,
                'label' => 'All'
            ]
        ];
    }
}
