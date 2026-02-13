<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Branch Management — English
    |--------------------------------------------------------------------------
    */

    // Navigation
    'navigation_group'       => 'Human Resources',
    'navigation_label'       => 'Branches',
    'model_label'            => 'Branch',
    'plural_model_label'     => 'Branches',

    // Form Sections
    'identity_section'       => 'Branch Identity',
    'geolocation_section'    => 'Geolocation & Geofencing',
    'geolocation_description'=> 'Click on the map or drag the marker to set the branch location',
    'shift_section'          => 'Shift & Policies',
    'address_section'        => 'Address',
    'financial_section'      => 'Financial Data',

    // Fields
    'name_ar'                => 'Name (Arabic)',
    'name_en'                => 'Name (English)',
    'code'                   => 'Branch Code',
    'phone'                  => 'Phone',
    'email'                  => 'Email',
    'is_active'              => 'Active',
    'latitude'               => 'Latitude',
    'longitude'              => 'Longitude',
    'geofence_radius'        => 'Geofence Radius',
    'geofence_radius_help'   => 'From 1m to 100,000m — no restrictions',
    'map_picker'             => 'Pick Location on Map',
    'map_picker_help'        => 'Click the map to place the marker, or drag the marker to fine-tune the position',
    'shift_start'            => 'Shift Start',
    'shift_end'              => 'Shift End',
    'grace_period'           => 'Grace Period',
    'address_ar'             => 'Address (Arabic)',
    'address_en'             => 'Address (English)',
    'city_ar'                => 'City (Arabic)',
    'city_en'                => 'City (English)',
    'salary_budget'          => 'Monthly Salary Budget',
    'delay_losses'           => 'Monthly Delay Losses',
    'employees_count'        => 'Employees',
    'created_at'             => 'Created At',
    'currency_sar'           => 'SAR',
    'meters'                 => 'meters',
    'minutes'                => 'minutes',

    // Financial Summary Section
    'financial_summary_section'       => 'Financial & Operational Summary',
    'financial_summary_description'   => 'Auto-calculated indicators from employee and salary data — read-only',
    'active_employees_count'          => 'Active Employees',
    'active_employees_count_hint'     => 'Number of currently active employees in this branch — calculated automatically.',
    'employee_unit'                   => 'employees',
    'total_salaries_sum'              => 'Total Basic Salaries',
    'total_salaries_sum_hint'         => 'Sum of basic salaries for all active employees — used as a reference for budget.',
    'branch_vpm'                      => 'Value Per Minute (VPM)',
    'branch_vpm_hint'                 => 'Cost per minute = Budget ÷ (employees × working days × shift hours × 60). Each minute of delay costs the branch this amount.',
    'minute_unit'                     => 'min',
    'monthly_loss_rate'               => 'Loss Rate vs Budget',
    'monthly_loss_rate_hint'          => 'Monthly delay losses as a percentage of salary budget — higher means more waste.',

    // Bulk Actions
    'bulk_update_geofence'            => 'Bulk Update Geofence',
    'bulk_geofence_updated'           => 'Geofence Updated',
    'bulk_geofence_updated_body'      => 'Geofence radius updated for :count branches.',
    'bulk_change_shift'               => 'Bulk Change Shift',
    'bulk_shift_updated'              => 'Shift Updated',
    'bulk_shift_updated_body'         => 'Shift times updated for :count branches.',
];
