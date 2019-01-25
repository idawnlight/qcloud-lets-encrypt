<?php

return [
    // Tencent Cloud API Config
    // CAM do not support SSL management, so use the main account
    'Tencent' => [
        'SecretId'       => 'AK********************', // API Secret ID
        'SecretKey'      => 'c2**********************', // API Secret Key
        'RequestMethod'  => 'POST', // Keep it
        'DefaultRegion'  => 'gz' // Keep it
    ],
    'ACME' => [
        // Powered by acme.sh (https://github.com/Neilpang/acme.sh)
        'issue' => [
            'domains' => [
                'example.com',
                '*.example.com',
                '*.another-example.com',
                '*.another-example-2.com'
            ],
            'verify' => [
                // Support ONLY dns, all domains above should be included under the account
                // See https://github.com/Neilpang/acme.sh/blob/master/dnsapi/README.md
                // DO NOT SUPPORT special types like OVH, Knot (knsupdate) or nsupdate
                // You may only be able to use one dns provider at a time
                'type' => 'dns_cf',
                'data' => [
                    'CF_Key' => 'sdfsdfsdfljlbjkljlkjsdfoiwje',
                    'CF_Email' => 'xxxx@sss.com'
                ]
            ]
        ],
    ],
    'CDN' => [
        'enable' => false,
        // Auto deploy for Tencent Cloud CDN
        // Only 'running' cdns will be updated
        'domains' => [
            // Then all the sub-domains of example.com will be updated with the new certificate, so issue a wildcard
            'example.com'
        ],
        'subDoamins' => [
            // Only sub1.another-example.com and sub2.another-example-2.com will be updated
            'sub1.another-example.com',
            'sub2.another-example-2.com'
        ]
    ]
];