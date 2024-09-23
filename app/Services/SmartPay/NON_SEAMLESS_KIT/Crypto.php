<?php

namespace App\Services\SmartPay\NON_SEAMLESS_KIT;

class Crypto
{
    protected $method = 'AES-256-GCM';

    /**
     * Encrypt the plain text.
     *
     * @param string $plainText
     * @param string $key
     * @return string
     */
    public function encrypt($plainText, $key)
    {
        if (is_array($plainText)) {
            $plainText = json_encode($plainText);
        }
        $initVector = openssl_random_pseudo_bytes(16);
        $openMode = openssl_encrypt($plainText, $this->method, $key, OPENSSL_RAW_DATA, $initVector, $tag);
        return bin2hex($initVector) . bin2hex($openMode . $tag);
    }

    /**
     * Decrypt the encrypted text.
     *
     * @param string $encryptedText
     * @param string $key
     * @return string
     */
    public function decrypt($encryptedText, $key)
    {
        $encryptedText = hex2bin($encryptedText);
        $iv_len = $tag_length = 16;
        $iv = substr($encryptedText, 0, $iv_len);
        $tag = substr($encryptedText, -$tag_length, $iv_len);
        $ciphertext = substr($encryptedText, $iv_len, -$tag_length);
        return openssl_decrypt($ciphertext, $this->method, $key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}
