<?php

use TencentCloud\Cdn\V20180606\CdnClient;
use TencentCloud\Cdn\V20180606\Models\DescribeDomainsConfigRequest;
use TencentCloud\Cdn\V20180606\Models\DomainFilter;
use TencentCloud\Cdn\V20180606\Models\ServerCert;
use TencentCloud\Cdn\V20180606\Models\UpdateDomainConfigRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Ssl\V20191205\Models\UploadCertificateRequest;
use TencentCloud\Ssl\V20191205\SslClient;

$config = require($conf);

set_error_handler(
    function ($severity, $message, $file, $line) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
);

function init() {
    global $config;
    $config['cred'] = new Credential($config['Tencent']['SecretId'], $config['Tencent']["SecretKey"]);
}

function uploadCert(string $cert, string $key) {
    global $config;
    try {
        $client = new SslClient($config['cred'], 'ap-shanghai');
        $req = new UploadCertificateRequest();
        $req->CertificatePublicKey = $cert;
        $req->CertificatePrivateKey = $key;
        $req->CertificateType = 'SVR';
        $resp = $client->UploadCertificate($req);
        return $resp->CertificateId;
    } catch (Exception $e) {
        throw $e;
    }
}

function depolyToCDN(string $domain, string $certId, array $parameters) {
    global $config;
    try {
        $client = new CdnClient($config['cred'], 'ap-shanghai');
        $query_req = new DescribeDomainsConfigRequest();
        $filter = new DomainFilter();
        $filter->Name = 'domain';
        $filter->Value = [$domain];
        $query_req->Filters = [$filter];
        $query_resp = $client->DescribeDomainsConfig($query_req)->Domains[0];

        $req = new UpdateDomainConfigRequest();
        $req->Domain = $query_resp->Domain;
        $req->Https = $query_resp->Https;
        $cert = new ServerCert();
        $cert->CertId = $certId;
        $req->Https->CertInfo = $cert;
        $req->Https->ClientCertInfo = null;
        return $client->UpdateDomainConfig($req);
    } catch (Exception $e) {
        throw $e;
    }
}