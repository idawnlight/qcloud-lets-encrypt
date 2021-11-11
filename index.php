<?php
$time_start = microtime(true);

require_once('vendor/autoload.php');

try {
    $options = getopt('c:k:', ['cert:', 'key:', 'conf:']);
    $cert = $options['c'] ?? $options['cert'] ?? null;
    $key = $options['k'] ?? $options['key'] ?? null;
    $certId = $options['certId'] ?? null;
    $conf = $options['conf'] ?? 'config.php';
    if (($cert ?? $key ?? null) ?? $certId === null) {
        echo <<<USAGE
Usage:
-c, --cert          full chain cert
-k, --key           cert key
--certId            certId on TencentCloud, will skip to deployment
--conf              config to use (default: config.php)
USAGE;
        exit;
    }

    require_once('lib.php');

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
            depolyToCDN($domain, $certId, $parameters);
        }
    }

    $time_end = microtime(true);
    $execution_time = $time_end - $time_start;
    echo "Finished in " . $execution_time . "s.\n";
} catch (Exception $e) {
    echo $e->getMessage();
    exit(1);
}
