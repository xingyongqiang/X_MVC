<?php

class Rsa
{
    //服务端密码
    private static $PRIVATE_KEY = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAJx+olYh1IVn85GToF+2iSyMbvcOXnbh5B02cWcxOOJrU6ugPg++lKtfNa3IatCE4oWqxt76h7I20BhjbSY+75N2MqoKr5sbR+JLTpRXV/7TFGe3KZUExvZYFAKDfQr3yY+zJQ8vGyh0HqRQSJBSJQBXznyUYpGw3a5Kd3aVgg2FAgMBAAECgYArnL7w8gygARBICbQv+FbSK3DhOQfqaZmA6qM/9t+7ed2fftDM4nfcKnRzMd9SrTXTunwhuAAJEm173nmUpwVsyDP9GEDMP1WKrS7HAu1s8HrPrjMfMuJ1YfNr6tP6lqtrgOVZU226HVT22v1tQpCy8zb8bSjvsr6mE5gaiHCDIQJBAP7yfKMLb1pBz36t69dvQkdsgp0b/fC8T7uyAU9bayOO1ehi7np9ol2G5ZVmgEzQyWylnm+UkkBgGJonRntUIncCQQCdJBHfbdXbr7PlgDFj7P/a00nIfjVTfFmgLyBJ/68ndcoaXGRPmLyKxo7ZjCpClQ/0GmhYiPfDRJwMgx9jBfLjAkEA/Q99H+oN0a1ZZQkF/IX3aCYRUBmk6vxAuLJsEnVP16/ELDNnPDbQn71yzeU8nQLxrOKIbYEv2q6IPRuXHnvY6QJAUhARKV5hrZ1/VB3zLR0KrItk38hRLu0knQufUCWvoerYhZW0aQD5jXuOBDw3oZfYwgC8d2foA9ijqcEcNglYQwJBANwfYsLiuHcmDg8qNwHHJQ1emtYSMlsmDzEaTRog74vKIShZkrROSlQztHDloqguRU5MZtd9WneQ3Amh5dYUchc=
-----END PRIVATE KEY-----';
    //服务端公钥
    private static $PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCcfqJWIdSFZ/ORk6BftoksjG73Dl524eQdNnFnMTjia1OroD4PvpSrXzWtyGrQhOKFqsbe+oeyNtAYY20mPu+TdjKqCq+bG0fiS06UV1f+0xRntymVBMb2WBQCg30K98mPsyUPLxsodB6kUEiQUiUAV858lGKRsN2uSnd2lYINhQIDAQAB
-----END PUBLIC KEY-----';

    /**
     * 获取私钥
     * @return bool|resource
     */
    private static function getPrivateKey()
    {
        $privateKey = self::$PRIVATE_KEY;
        return openssl_pkey_get_private($privateKey);
    }

    /**
     * 获取公钥
     * @return bool|resource
     */
    private static function getPublicKey()
    {
        $publicKey = self::$PUBLIC_KEY;
        return openssl_pkey_get_public($publicKey);
    }

    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public static function privateEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, self::getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public static function publicEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data, $encrypted, self::getPublicKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public static function privateDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey())) ? $decrypted : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public static function publicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey())) ? $decrypted : null;
    }

    /**
     * 创建签名
     * @param string $data 数据
     * @return null|string
     */
    public function createSign($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_sign($data, $sign, self::getPrivateKey(), OPENSSL_ALGO_SHA256) ? base64_encode($sign) : null;
    }

    /**
     * 验证签名
     * @param string $data 数据
     * @param string $sign 签名
     * @return bool
     */
    public function verifySign($data = '', $sign = '')
    {
        if (!is_string($sign) || !is_string($sign)) {
            return false;
        }
        return (bool)openssl_verify($data, base64_decode($sign), self::getPublicKey(), OPENSSL_ALGO_SHA256);
    }
}