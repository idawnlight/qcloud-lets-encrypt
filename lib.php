<?php
$config = require($conf);

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

function uploadCert(string $cert, string $key) {
    global $config;
    $service = QcloudApi::load('wss', $config['Tencent']);
    $package = array(
        'cert' => $cert,
        'certType' => 'SVR',
        'key' => $key,
        'SignatureMethod' => 'HmacSHA256',
    );
    $result = $service->CertUpload($package);
    
    if ($result === false) {
        $error = $service->getError();
        echo "Error code: " . $error->getCode() . '  Message: ' . $error->getMessage();
        throw new Exception($error->getMessage(), $error->getCode());
        return false;
    } else {
        return $result['data']['id'];
    }
}

function depolyToCDN(string $domain, string $certId, array $parameters) {
    global $config;
    $service = QcloudApi::load('cdn', $config['Tencent']);
    $package = array(
        'host' => $domain,
        'certId' => $certId,
        'SignatureMethod' => 'HmacSHA256',
    );
    $package = array_merge($package, $parameters);
    $result = $service->SetHttpsInfo($package);
    
    if ($result === false) {
        $error = $service->getError();
        echo "Error code: " . $error->getCode() . '  Message: ' . $error->getMessage();
        throw new Exception($error->getMessage(), $error->getCode());
        return false;
    } else {
        return $result;
    }
}