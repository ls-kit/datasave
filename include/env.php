<?php
define('BASEROW_TOKEN', 'Lua411iXgFmhWeJ56tKmxW7H8bSZ3sL8');
// define('BASEROW_BASE_URL', 'https://api.baserow.io/api/database/rows/table/');

// define('BASEROW_TOKEN', 'your_actual_token');
define('BASEROW_BASE_URL', 'https://api.baserow.io/api/');


$baserow_tables = [
  'calculation' => '543529',
  'user' => '543530',
  'clients' => '543531',
  'plan' => '543532'
];

$baserow_fields = [
    'calculation' => [
        'id'             => 'field_',
        'Name'           => 'field_4339371',
        'Notes'          => 'field_4339372',
        'cal_id'         => 'field_4339373',
        'hscode'         => 'field_4339417',
        'Assess Val'     => 'field_4339419',
        'CD Amt'         => 'field_4339420',
        'RD Amt'         => 'field_4339421',
        'VAT Base'       => 'field_4339423',
        'Imp VAT'        => 'field_4339424',
        'AT'             => 'field_4339425',
        'Total Duty'     => 'field_4339426',
        'Pur/PKG'        => 'field_4339427',
        'Add Val/PKG'    => 'field_4339428',
        'Sale Val/PKG'   => 'field_4339429',
        'Tot Sales'      => 'field_4339430',
        'Sales Vat'      => 'field_4339431',
        'Challan Value'  => 'field_4339435',
    ],
    'user' => [
        'id'                 => 'field_',
        'Name'               => 'field_4339378',
        'email'              => 'field_4339379',
        'phone'              => 'field_4339380',
        'current_plan_id '   => 'field_4339390',
        'status'             => 'field_4339391',
        'created_at'         => 'field_4339392',
        'subscriptions'      => 'field_4339393',
        'payment_logs'       => 'field_4339394',
        'Password'           => 'field_4339395',
        'Role'               => 'field_4339396',
    ],
    'clients' =>[
        'id' => '',
        'Name' => 'field_4339383',
        'Notes' => 'field_4339384',
        'Active' => 'field_4339385',
    ],
    'plan' => [
        'id' => '',
        'Name' => 'field_4339386',
        'Notes' => 'field_4339387',
        'Active' => 'field_4339388',
    ]
];
