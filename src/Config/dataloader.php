<?php

// Please refer to below examples and choose one to implement

// ---------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------BY REGION EXAMPLE--------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/**
return [
    'by_region' => true,
    'supported_region' => ['GC',],
    'supported_type_region' => [
        'GC' => ['SAP',]
    ],
    'path' => 'datasource/%s/%s', // datasource/[Type]/[Region]
    'report' => 'report',
    'history' => 'history',
    'mailbox' => [
        'host' => env('SYS_EMAIL_HOST', '{mail.apple.com:993/imap/ssl/novalidate-cert}INBOX'),
        'user'=> env('SYS_EMAIL_USERNAME', ''),
        'pass'=> env('SYS_EMAIL_PASSWORD', ''),
    ],
    'source' => 'Mail', //Mail or Box , box is not implemented yet
    'admin' => [
        'GC' => 'prashant_balan@apple.com',
    ],
    'SAP' => [
        'GC' => [
            'from' => env('ADMIN_MAIL_GC', 'prashant_balan@apple.com'),
            'subject'=> env('DATALOADER_SAP_SUBJECT', '[ORT]'),
            'inbox' => env('DATALOADER_SAP_INBOX','ORT-GLOBAL-REPORT-DATA'),
            'FBL5N' => env('DATALOADER_FBL5N_FILENAME_PATTERN', 'ort_fbl5n'), // file pattern matched to download the file
            'FBL1N' => env('DATALOADER_FBL1N_FILENAME_PATTERN','ort_fbl1n'), // file pattern matched to download the file
            'move_to'=> env('DATALOADER_SAP_MOVETO', 'ORT-BK-GC'),
            'subtype' => ['FBL5N','FBL1N'],
        ]
    ]
];
**/

// ---------------------------------------------------------------------------------------------------------------------
// --------------------------------------------------NO REGION EXAMPLE--------------------------------------------------
// ---------------------------------------------------------------------------------------------------------------------
/**
return [
    'by_region' => false,
    'supported_region' => [],
    'supported_type_region' => [
        'SAP',
    ],
    'path' => 'datasource/%s', // datasource/[Type]
    'report' => 'report',
    'history' => 'history',
    'mailbox' => [
        'host' => env('SYS_EMAIL_HOST', '{mail.apple.com:993/imap/ssl/novalidate-cert}INBOX'),
        'user'=> env('SYS_EMAIL_USERNAME', ''),
        'pass'=> env('SYS_EMAIL_PASSWORD', ''),
    ],
    'source' => 'Mail', //Mail or Box , box is not implemented yet
    'admin' => [
        'prashant_balan@apple.com',
    ],
    'SAP' => [
        'from' => env('ADMIN_MAIL_GC', 'prashant_balan@apple.com'),
        'subject'=> env('DATALOADER_SAP_SUBJECT', '[ORT]'),
        'inbox' => env('DATALOADER_SAP_INBOX','ORT-GLOBAL-REPORT-DATA'),
        'FBL5N' => env('DATALOADER_FBL5N_FILENAME_PATTERN', 'ort_fbl5n'), // file pattern matched to download the file
        'FBL1N' => env('DATALOADER_FBL1N_FILENAME_PATTERN','ort_fbl1n'), // file pattern matched to download the file
        'move_to'=> env('DATALOADER_SAP_MOVETO', 'ORT-BK'),
        'subtype' => ['FBL5N','FBL1N'],
    ],
];
*/

return [

];
