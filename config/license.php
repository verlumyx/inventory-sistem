<?php

return [
    /*
    |--------------------------------------------------------------------------
    | License Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the license system
    |
    */

    /**
     * Email addresses of system administrators who will receive license codes
     */
    'administrators' => [
        env('LICENSE_ADMIN_EMAIL_1', 'admin@sistema.com'),
        env('LICENSE_ADMIN_EMAIL_2', 'admin@sistema.com'),
        // Add more administrator emails here
        // 'admin2@sistema.com',
        // 'admin3@sistema.com',
    ],

    /**
     * License duration in months
     */
    'duration_months' => env('LICENSE_DURATION_MONTHS', 6),

    /**
     * Days before expiration to show warnings
     */
    'warning_days' => [30, 15, 7, 3, 1],

    /**
     * Grace period in days after expiration
     */
    'grace_period_days' => env('LICENSE_GRACE_PERIOD_DAYS', 3),

    /**
     * Enable/disable license system
     */
    'enabled' => env('LICENSE_ENABLED', true),

    /**
     * Email configuration for license notifications
     */
    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@sistema.com'),
        'from_name' => env('MAIL_FROM_NAME', 'Sistema de Inventario'),
    ],
];
