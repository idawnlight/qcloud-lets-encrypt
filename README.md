# 在腾讯云上使用 Let's Encrypt 证书

腾讯云并不支持自动签发 Let's Encrypt 证书，而手动上传证书并在 CDN 中启用会相当麻烦，这个脚本用于自动部署已签发 / 续期的证书到腾讯云证书管理并部署到腾讯云 CDN

# 用法

1. 将 `config.example.php` 复制为 `config.php`，根据提示完成相关配置
2. 以 [acme.sh](https://github.com/Neilpang/acme.sh) 为例，在 `~/.acme.sh/[main domain]/[main domain].conf` 中配置 `Le_RenewHook` 字段，以下为示例

```bash
~/.acme.sh/acme.sh\
 --install-cert\
 -d [main domain]\
 --fullchain-file /tmp/cert.cer\
 --key-file /tmp/cert.key\
&& php /some/path/index.php
 --cert /tmp/cert.cer
 --key /tmp/cert.key
&& rm -f /tmp/cert.cer /tmp/cert.key
```
