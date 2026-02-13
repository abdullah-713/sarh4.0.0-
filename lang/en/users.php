<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Management — English
    |--------------------------------------------------------------------------
    */

    // Navigation
    'navigation_group'       => 'Human Resources',
    'navigation_label'       => 'Employees',
    'model_label'            => 'Employee',
    'plural_model_label'     => 'Employees',

    // Form Sections
    'profile_section'        => 'Profile Picture',
    'core_info_section'      => 'Core Information',
    'core_info_description'  => 'The Core Four: Name, Email, Password, and Salary',
    'financial_section'      => 'Financial Information',
    'financial_description'  => 'Basic salary is essential for cost-per-minute calculation',
    'organization_section'   => 'Organizational Details',

    // Fields
    'avatar'                 => 'Profile Picture',
    'name_ar'                => 'Name (Arabic)',
    'name_en'                => 'Name (English)',
    'email'                  => 'Email Address',
    'password'               => 'Password',
    'basic_salary'           => 'Basic Salary',
    'housing_allowance'      => 'Housing Allowance',
    'transport_allowance'    => 'Transport Allowance',
    'other_allowances'       => 'Other Allowances',
    'branch'                 => 'Branch',
    'department'             => 'Department',
    'role'                   => 'Role',
    'direct_manager'         => 'Direct Manager',
    'phone'                  => 'Phone',
    'employee_id'            => 'Employee ID',
    'security_level'         => 'Security Level',
    'status'                 => 'Status',
    'employment_type'        => 'Employment Type',
    'created_at'             => 'Created At',
    'currency_sar'           => 'SAR',

    // Status Options
    'status_active'          => 'Active',
    'status_suspended'       => 'Suspended',
    'status_terminated'      => 'Terminated',
    'status_on_leave'        => 'On Leave',

    // Employment Type Options
    'type_full_time'         => 'Full-time',
    'type_part_time'         => 'Part-time',
    'type_contract'          => 'Contract',
    'type_intern'            => 'Intern',

    // Points Management (v1.7.0)
    'total_points'           => 'Excellence Points',
    'adjust_points'          => 'Adjust Points',
    'points_amount'          => 'Points Amount',
    'points_helper'          => 'Positive to add, negative to deduct',
    'points_reason'          => 'Reason for Adjustment',
    'points_adjusted'        => 'Points Adjusted',
    'points_adjusted_body'   => ':points points added to :name',

    // Bulk Actions
    'bulk_adjust_salary'              => 'Bulk Adjust Salaries',
    'adjustment_type'                 => 'Adjustment Type',
    'adjustment_set'                  => 'Set Fixed Amount',
    'adjustment_add'                  => 'Add Amount',
    'adjustment_percent'              => 'Percentage Increase',
    'adjustment_amount'               => 'Amount / Percentage',
    'adjustment_amount_helper'        => 'Enter amount in SAR or percentage based on the selected adjustment type',
    'bulk_salary_updated'             => 'Salaries Updated',
    'bulk_salary_updated_body'        => 'Salaries updated for :count employees.',
    'bulk_change_branch'              => 'Transfer to Branch',
    'bulk_branch_updated'             => 'Employees Transferred',
    'bulk_branch_updated_body'        => ':count employees transferred to the new branch.',
    'bulk_change_status'              => 'Bulk Change Status',
    'bulk_status_updated'             => 'Status Updated',
    'bulk_status_updated_body'        => 'Status updated for :count employees.',
];
