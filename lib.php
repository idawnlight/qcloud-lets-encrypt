<?php

use TencentCloud\Cdn\V20180606\CdnClient;
use TencentCloud\Cdn\V20180606\Models\DescribeDomainsConfigRequest;
use TencentCloud\Cdn\V20180606\Models\DomainFilter;
use TencentCloud\Cdn\V20180606\Models\ServerCert;
use TencentCloud\Cdn\V20180606\Models\UpdateDomainConfigRequest;
use TencentCloud\Common\Credential;
use TencentCloud\Ssl\V20191205\Models\UploadCertificateRequest;
use TencentCloud\Ssl\V20191205\Models\ModifyCertificatesExpiringNotificationSwitchRequest;
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

function deployToCDN(string $domain, string $certId, array $parameters) {
    global $config;
    try {
        $client = new CdnClient($config['cred'], 'ap-shanghai');
        $query_req = new DescribeDomainsConfigRequest();
        $filter = new DomainFilter();
        $filter->Name = 'domain';
        $filter->Value = [$domain];
        $query_req->Filters = [$filter];
        $query_resp = $client->DescribeDomainsConfig($query_req)->Domains[0];

        if ($query_resp->Https->CertInfo && $query_resp->Https->CertInfo->CertId && ($parameters['disableExpireNotification'] ?? false)) {
            disableExpireNotification($query_resp->Https->CertInfo->CertId);
        }

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

function disableExpireNotification(string $certId) {
    global $config;
    try {
        $client = new SslClient($config['cred'], 'ap-shanghai');
        $req = new ModifyCertificatesExpiringNotificationSwitchRequest();
        $req->CertificateIds = [$certId];
        $req->SwitchStatus = 1;
        return $client->ModifyCertificatesExpiringNotificationSwitch($req);
    } catch (Exception $e) {
        throw $e;
    }
}

class DogeCloud {
    private $accessKey;
    private $secretKey;

    public function __construct($accessKey, $secretKey) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function uploadCert(string $cert, string $private) {
        $api = $this->req('/cdn/cert/upload.json', array(
            'note' => 'idawnlight/qcloud-lets-encrypt @ ' . time(),
            'cert' => $cert,
            'private' => $private
        ), true);

        if ($api && $api['code'] == 200) {
            // 成功
            $certId = $api['data']['id'];
            return $certId;
        } else {
            var_dump($api['msg'] ?? 'Error'); // 失败
            return null;
        }
    }

    public function deployToCDN(string $domain, string $certId) {
        $api = $this->req('/cdn/domain/config.json?domain=' . $domain, array(
            'cert_id' => $certId
        ), true);
        if ($api && $api['code'] == 200) {
            return true;
        } else {
            var_dump($api['msg'] ?? 'Error'); // 失败
            return false;
        }
    }

    /**
     * 调用多吉云 API
     *
     * @param string    $apiPath    调用的 API 接口地址，包含 URL 请求参数 QueryString，例如：/console/vfetch/add.json?url=xxx&a=1&b=2
     * @param array     $data       POST 的数据，关联数组，例如 array('a' => 1, 'b' => 2)，传递此参数表示不是 GET 请求而是 POST 请求
     * @param boolean   $jsonMode   数据 data 是否以 JSON 格式请求，默认为 false 则使用表单形式（a=1&b=2）
     * 
     * @author 多吉云
     * @return array 返回的数据
     */ 
    private function req($apiPath, $data = array(), $jsonMode = false) {
        $body = $jsonMode ? json_encode($data) : http_build_query($data);
        $signStr = $apiPath . "\n" . $body;
        $sign = hash_hmac('sha1', $signStr, $this->secretKey);
        $Authorization = "TOKEN " . $this->accessKey . ":" . $sign;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.dogecloud.com" . $apiPath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // 如果是本地调试，或者根本不在乎中间人攻击，可以把这里的 1 和 2 修改为 0，就可以避免报错
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 建议实际使用环境下 cURL 还是配置好本地证书
        if(isset($data) && $data){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: ' . ($jsonMode ? 'application/json' : 'application/x-www-form-urlencoded'),
            'Authorization: ' . $Authorization
        ));
        $ret = curl_exec($ch);
        curl_close($ch);
        return json_decode($ret, true);
    }
}