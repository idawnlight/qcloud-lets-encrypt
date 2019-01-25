<?php
require_once('vendor/autoload.php');

global $config;
$config = require('config.php');
//var_dump($config);

main_handler([], []);

function main_handler($event, $context) {
    global $config;
    try {
        echo "Checking exist cerificate\n";
        $certs = getCertList();
        //var_dump($certs);
        $current = [];
        $compare_2 = $config['ACME']['issue']['domains'];
        sort($compare_2);
        foreach ($certs as $cert) {
            $compare_1 = $cert['subjectAltName'];
            sort($compare_1);
            if ($compare_1 == $compare_2 && strstr($cert['productZhName'], 'et\'s')) {
                $current = $cert;
                break;
            }
        }
        if ($current == [] || time() - strtotime($current['certEndTime']) >= 2592000) {
            if ($current == []) echo "Not found, start issuing\n";
            else echo "Found " . $current['id'] . ", start renewing\n";
            issueNewCert();
        } else {
            return 'Already up-to-date';
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function getCertList() {
    global $config;
    $service = QcloudApi::load('wss', $config['Tencent']);
    $package = array(
        'count' => 100,
        // 'Region' => 'sh', // 当Region不是上面配置的DefaultRegion值时，可以重新指定请求的Region
        'SignatureMethod' => 'HmacSHA256',//指定所要用的签名算法，可选HmacSHA256或HmacSHA1，默认为HmacSHA1
    );
    $certList = $service->CertGetList($package);
    
    if ($certList === false) {
        $error = $service->getError();
        echo "Error code: " . $error->getCode() . '  Message: ' . $error->getMessage();
        throw new Exception($error->getMessage(), $error->getCode());
        return false;
    } else {
        return $certList['data']['list'];
    }
}

function issueNewCert() {
    global $config;
    echo "Preparing acme.sh\n";
}