<?php

return [
    'driver' => env('PDF_DRIVER', 'browsershot'),

    'browsershot' => [
        'node_path'   => env('BROWSERSHOT_NODE_PATH', 'node'),
        'chrome_path' => env('BROWSERSHOT_CHROME_PATH', '/usr/bin/chromium-browser'),
    ],

    'pdfshift' => [
        'api_key' => env('PDFSHIFT_API_KEY'),
        'endpoint'=> 'https://api.pdfshift.io/v3/convert/pdf',
    ],
];
