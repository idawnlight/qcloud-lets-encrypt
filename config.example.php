<?php

return [
    // Tencent Cloud API Config
    // CAM currently do not support SSL management, so we can only use the main account
    // For some reason, we can only use API v2
    'Tencent' => [
        'SecretId'       => 'AK********************', // API Secret ID
        'SecretKey'      => 'c2**********************', // API Secret Key
        'RequestMethod'  => 'POST', // Keep it
        'DefaultRegion'  => 'gz' // Keep it
    ],
    'CDN' => [
        'enable' => false,
        // Auto deploy for Tencent Cloud CDN (China Mainland)
        // Only 'running' cdn instances will be updated
        'domains' => [
            // Only sub1.another-example.com and sub2.another-example-2.com will be updated
            // Refer to https://cloud.tencent.com/document/api/228/12965 for parameters, which are optional
            'sub1.another-example.com' => [
                'httpsType' => 4, // ONLY 3 or 4
                'forceSwitch' => 3,
                'http2' => 'on'
            ],
            'sub2.another-example-2.com' => [] // without parameters
        ]
    ]
];