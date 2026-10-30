<?php

/*
| Mobile wallets offered at checkout. Customers scan the QR code or send money to the
| phone number, then upload a screenshot of the transfer for staff to confirm.
|
| QR images live in public/img/payments/ — replace the files to change the codes.
| When a QR file is missing, checkout shows only the phone number.
*/

return [
    'wallets' => [
        'kpay' => [
            'name' => 'KBZPay (KPay)',
            'short' => 'KPay',
            'phone' => env('KPAY_PHONE', '09788985027'),
            'account_name' => env('KPAY_ACCOUNT_NAME', 'SAI THUREIN KHANT'),
            'qr' => 'img/payments/kpay-qr.png',
            'color' => '#0055a5',
        ],
        'ayapay' => [
            'name' => 'AYA Pay',
            'short' => 'AYA Pay',
            'phone' => env('AYAPAY_PHONE', '09788985027'),
            'account_name' => env('AYAPAY_ACCOUNT_NAME', 'SAI THU RAIN KHANT'),
            'qr' => 'img/payments/ayapay-qr.png',
            'color' => '#c8102e',
        ],
    ],
];
