<?php
$time_start = microtime(true);

require_once('vendor/autoload.php');

try {
    $options = getopt('c:k:', ['cert:', 'key:', 'certId:', 'conf:']);
    $cert = $options['c'] ?? $options['cert'] ?? null;
    $key = $options['k'] ?? $options['key'] ?? null;
    $certId = $options['certId'] ?? null;
    $conf = $options['conf'] ?? 'config.php';
    if ((($cert ?? $key ?? null) ?? $certId) === null) {
        echo <<<USAGE
Usage:
-c, --cert          full chain cert
-k, --key           cert key
--certId            certId on Tencent Cloud / DogeCloud, will skip to deployment
--conf              config to use (default: config.php)
USAGE;
        exit;
    }

    require_once('lib.php');

    // run if the 'enable' key is not set or set to true
    if ($config['Tencent']['enable'] ?? true) {
        echo "Using Tencent Cloud.\n";

        init();

        if ($certId === null) {
            $cert = file_get_contents($cert);
            $key = file_get_contents($key);
    
            $certId = uploadCert($cert, $key);
    
            echo "New cert " . $certId . ".\n";
        } else {
            echo "Existing cert " . $certId . ".\n";
        }
    
        if ($config['CDN']['enable']) {
            foreach ($config['CDN']['domains'] as $domain => $parameters) {
                deployToCDN($domain, $certId, $parameters);
            }
        }
    } else if ($config['dogecloud']['enable'] ?? true) {
        echo "Using DogeCloud.\n";

        $dogecloud = new DogeCloud($config['dogecloud']['credentials']['accessKey'], $config['dogecloud']['credentials']['secretKey']);

        if ($certId === null) {
            $cert = file_get_contents($cert);
            $key = file_get_contents($key);
    
            $certId = $dogecloud->uploadCert($cert, $key);
            if ($certId === null) {
                throw new Exception("Failed to upload cert.");
            }
    
            echo "New cert " . $certId . ".\n";
        } else {
            echo "Existing cert " . $certId . ".\n";
        }

        if ($config['dogecloud']['cdn']['domains'] ?? null) {
            foreach ($config['dogecloud']['cdn']['domains'] as $domain) {
                if (!$dogecloud->deployToCDN($domain, $certId)) {
                    throw new Exception("Failed to deploy to CDN.");
                }
            }
        }
    }

    $time_end = microtime(true);
    $execution_time = $time_end - $time_start;
    echo "Finished in " . $execution_time . "s.\n";
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}
