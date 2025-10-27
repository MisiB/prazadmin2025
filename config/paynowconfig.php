<?php

return [
    'name' => 'Api',
    'paymenturl' => 'https://payment.praz.org.zw/paymentportal/invoice',
    'default_currency' => 3,
    'return_url' => env('PAYNOW_RETURN_URL', 'https://prazpayments.test/paynow/'),
    'mode' => env('PAYNOW_MODE', 'test'),
];
