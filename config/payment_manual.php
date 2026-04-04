<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rekening & e-wallet untuk pembayaran manual (siswa)
    |--------------------------------------------------------------------------
    | Isi di .env. Metode tanpa nomor/ID tidak ditampilkan di halaman manual.
    */

    'banks' => [
        'bri' => [
            'label' => 'Bank BRI',
            'account_number' => env('PAYMENT_BRI_ACCOUNT'),
            'account_name' => env('PAYMENT_BRI_NAME'),
        ],
        'bca' => [
            'label' => 'Bank BCA',
            'account_number' => env('PAYMENT_BCA_ACCOUNT'),
            'account_name' => env('PAYMENT_BCA_NAME'),
        ],
        'bni' => [
            'label' => 'Bank BNI',
            'account_number' => env('PAYMENT_BNI_ACCOUNT'),
            'account_name' => env('PAYMENT_BNI_NAME'),
        ],
    ],

    'ewallets' => [
        'dana' => [
            'label' => 'DANA',
            'account_id' => env('PAYMENT_DANA_ID'),
            'account_name' => env('PAYMENT_DANA_NAME'),
        ],
        'gopay' => [
            'label' => 'GoPay',
            'account_id' => env('PAYMENT_GOPAY_ID'),
            'account_name' => env('PAYMENT_GOPAY_NAME'),
        ],
        'shopeepay' => [
            'label' => 'ShopeePay',
            'account_id' => env('PAYMENT_SHOPEEPAY_ID'),
            'account_name' => env('PAYMENT_SHOPEEPAY_NAME'),
        ],
    ],

    /*
    | Path relatif dari folder public, contoh: images/payment/qris.png
    */
    'qris' => [
        'public_path' => env('PAYMENT_QRIS_PATH', 'images/payment/qris.svg'),
    ],

];
