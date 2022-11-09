<?php

return [
    // Tencent Cloud API Credential
    'Tencent' => [
        'SecretId'       => 'AK********************', // API Secret ID
        'SecretKey'      => 'c2**********************', // API Secret Key
    ],
    'CDN' => [
        'enable' => false,
        // Automatic deployment for Tencent Cloud CDN (China Mainland)
        // Only 'running' cdn instances will be updated
        'domains' => [
            // Only sub1.another-example.com and sub2.another-example-2.com will be updated
            'sub1.another-example.com' => [
                // This option will disable the expire notification of old certificate when set to true
                // You may want to ensure all cdn instances are updated before enabling this
                'disableExpireNotification' => false
            ],
            'sub2.another-example-2.com' => [] // All parameters are optional
        ]
    ]
];