<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    |
    | Currencies employers may quote job salaries in. Add new entries here
    | and they automatically become available in every salary selector.
    |
    */

    'currencies' => [
        'NGN' => ['symbol' => '₦', 'name' => 'Nigerian Naira'],
        'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
        'ZAR' => ['symbol' => 'R', 'name' => 'South African Rand'],
        'GHS' => ['symbol' => 'GH₵', 'name' => 'Ghanaian Cedi'],
        'KES' => ['symbol' => 'KSh', 'name' => 'Kenyan Shilling'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Salary Periods
    |--------------------------------------------------------------------------
    */

    'salary_periods' => [
        'hourly' => 'Hour',
        'daily' => 'Day',
        'weekly' => 'Week',
        'monthly' => 'Month',
        'yearly' => 'Year',
    ],
];
