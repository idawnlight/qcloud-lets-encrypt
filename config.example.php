<?php

return [
    // Tencent Cloud API Config
    'Tencent' => [
        'SecretId'       => 'AK********************', // API Secret ID
        'SecretKey'      => 'c2**********************', // API Secret Key
        // 'RequestMethod'  => 'POST', // Deprecated
        // 'DefaultRegion'  => 'ap-shanghai' // Seems useless
    ],
    'CDN' => [
        'enable' => false,
        // Auto deploy for Tencent Cloud CDN (China Mainland)
        // Only 'running' cdn instances will be updated
        'domains' => [
            // Only sub1.another-example.com and sub2.another-example-2.com will be updated
            // Note: parameters are deprecated, not passed to api
            'sub1.another-example.com' => [],
            'sub2.another-example-2.com' => [] // without parameters
        ]
    ]
];