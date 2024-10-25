<?php

namespace App\Service;

class EncryptionService {

    private string $secretKey;
    private string $cipher;

    public function __construct(string $secretKey = 'je_suis_content_pour_toi_mars_et_avril_aussi', string $cipher = 'AES-256-CBC') {
        $this->secretKey = $secretKey;
        $this->cipher = $cipher;
    }

    public function encrypt(string $data): string {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = openssl_encrypt($data, $this->cipher, $this->secretKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encryptedData): string {
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, $this->cipher, $this->secretKey, 0, $iv);
    }
}
