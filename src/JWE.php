<?php

namespace Saitama\QR;

if (!strpos(__DIR__, 'vendor')) {
	include_once(__DIR__ . '/../vendor/autoload.php');
}

use Exception;
use phpseclib\Crypt\RSA;
use phpseclib\Crypt\AES;
use phpseclib\Crypt\Random;

require_once "JWT.php";
require_once "JWK.php";

class JWE extends JWT {
    public $plain_text;
    public $cipher_text;
    public $content_encryption_key;
    public $jwe_encrypted_key;
    public $encryption_key;
    public $mac_key;
    public $iv;
    public $authentication_tag;
    public $auth_data;

    /**
    * The constructor function initializes the Payload for the JWE token.
    */
    public function __construct($input = null) {
        if ($input instanceof JWT) {
            $this->raw = $input->toString();
        } else {
            $this->raw = $input;
        }
        unset($this->header['typ']);
    }

    /**
     * The constructor function initializes the class variables and creates instances of the other classes
     * 
     * @return string for slash according to OS.
     */
    private function _getDirSlash()
    {
        return stripos(PHP_OS, 'win') === 0 ? "\\" : "/";
    }

    /**
    * The function decrypt the token with the private key or secret, copied from gree/jose package.
    * @param public_key_or_secret a pem file or secret string
    * @param algorithm a string of supported algorithm
    * @param encryption_method a string of supported encryption method
    *
    * @return JWE Token
    */
    public function encrypt($public_key_or_secret, $algorithm = 'RSA1_5', $encryption_method = 'A128CBC-HS256') {
        $this->header['alg'] = $algorithm;
        $this->header['enc'] = $encryption_method;
        if (
            $public_key_or_secret instanceof JWK &&
            !array_key_exists('kid', $this->header) &&
            array_key_exists('kid', $public_key_or_secret->components)
        ) {
            $this->header['kid'] = $public_key_or_secret->components['kid'];
        }
        $this->plain_text = $this->raw;
        $this->generateContentEncryptionKey($public_key_or_secret);
        $this->encryptContentEncryptionKey($public_key_or_secret);
        $this->generateIv();
        $this->deriveEncryptionAndMacKeys();
        $this->encryptCipherText();
        $this->generateAuthenticationTag();
        return $this;
    }

    /**
    * The function decrypt the token with the private key or secret, copied from gree/jose package.
    * @param private_key_or_secret a pem file or secret string
    *
    * @return JWE Token
    */
    public function decrypt($private_key_or_secret) {
        $this->decryptContentEncryptionKey($private_key_or_secret);
        $this->deriveEncryptionAndMacKeys();
        $this->decryptCipherText();
        $this->checkAuthenticationTag();
        return $this;
    }

    /**
    * The function returns string token from an array, copied from gree/jose package.
    * 
    * @return Token String
    */
    public function toString() {
        return implode('.', array(
            $this->compact((object) $this->header),
            $this->compact($this->jwe_encrypted_key),
            $this->compact($this->iv),
            $this->compact($this->cipher_text),
            $this->compact($this->authentication_tag)
        ));
    }

    /**
    * The function decode the JWE token, copied from gree/jose package.
    * 
    * @return JWE Token
    */

    public function decode($jwt_string) {
        $segments = explode('.', $jwt_string);
        $jwe = new JWE($jwt_string);
        $jwe->auth_data  = $segments[0];
        $jwe->header     = (array) $jwe->extract($segments[0]);
        $jwe->jwe_encrypted_key  = $jwe->extract($segments[1], 'as_binary');
        $jwe->iv                 = $jwe->extract($segments[2], 'as_binary');
        $jwe->cipher_text        = $jwe->extract($segments[3], 'as_binary');
        $jwe->authentication_tag = $jwe->extract($segments[4], 'as_binary');
        return $jwe;
    }

    /**
    * The function set RSA key, copied from gree/jose package.
    * 
    * @return RSA key 
    */
    private function rsa($public_or_private_key, $padding_mode) {
        if ($public_or_private_key instanceof JWK) {
            $rsa = $public_or_private_key->toKey();
        } else if ($public_or_private_key instanceof RSA) {
            $rsa = $public_or_private_key;
        } else {
            $rsa = new RSA();
            $rsa->loadKey($public_or_private_key);
        }
        $rsa->setEncryptionMode($padding_mode);
        return $rsa;
    }

    /**
    * The function set cipher value, copied from gree/jose package.
    * 
    * @return String 
    */
    private function cipher() {
        switch ($this->header['enc']) {
            case 'A128GCM':
            case 'A256GCM':
                throw new Exception('Algorithm not supported');
            case 'A128CBC-HS256':
            case 'A256CBC-HS512':
                $cipher = new AES(AES::MODE_CBC);
                break;
            default:
                throw new Exception('Unknown algorithm');
        }
        switch ($this->header['enc']) {
            case 'A128GCM':
            case 'A128CBC-HS256':
                $cipher->setBlockLength(128);
                break;
            case 'A256GCM':
            case 'A256CBC-HS512':
                $cipher->setBlockLength(256);
                break;
            default:
                throw new Exception('Unknown algorithm');
        }
        return $cipher;
    }

    /**
    * The function computes the random bytes, copied from gree/jose package.
    * 
    * @return None 
    */
    private function generateRandomBytes($length) {
        return Random::string($length);
    }

    /**
    * The function generates the initialization vector, copied from gree/jose package.
    * 
    * @return None 
    */
    private function generateIv() {
        switch ($this->header['enc']) {
            case 'A128GCM':
            case 'A128CBC-HS256':
                $this->iv = $this->generateRandomBytes(128 / 8);
                break;
            case 'A256GCM':
            case 'A256CBC-HS512':
                $this->iv = $this->generateRandomBytes(256 / 8);
                break;
            default:
                throw new Exception('Unknown algorithm');
        }
    }

    /**
    * The function generates the encryptin key, copied from gree/jose package.
    * 
    * @return None 
    */
    private function generateContentEncryptionKey($public_key_or_secret) {
        if ($this->header['alg'] == 'dir') {
            $this->content_encryption_key = $public_key_or_secret;
        } else {
            switch ($this->header['enc']) {
                case 'A128GCM':
                case 'A128CBC-HS256':
                    $this->content_encryption_key = $this->generateRandomBytes(256 / 8);
                    break;
                case 'A256GCM':
                case 'A256CBC-HS512':
                    $this->content_encryption_key = $this->generateRandomBytes(512 / 8);
                    break;
                default:
                    throw new Exception('Unknown algorithm');
            }
        }
    }

    /**
    * The function encrypt the content base on the supported algorithm, copied from gree/jose package.
    * 
    * @return None 
    */
    private function encryptContentEncryptionKey($public_key_or_secret) {
        switch ($this->header['alg']) {
            case 'RSA1_5':
                $rsa = $this->rsa($public_key_or_secret, RSA::ENCRYPTION_PKCS1);
                $this->jwe_encrypted_key = $rsa->encrypt($this->content_encryption_key);
                break;
            case 'RSA-OAEP':
                $rsa = $this->rsa($public_key_or_secret, RSA::ENCRYPTION_OAEP);
                $this->jwe_encrypted_key = $rsa->encrypt($this->content_encryption_key);
                break;
            case 'dir':
                $this->jwe_encrypted_key = '';
                return;
            case 'A128KW':
            case 'A256KW':
            case 'ECDH-ES':
            case 'ECDH-ES+A128KW':
            case 'ECDH-ES+A256KW':
                throw new Exception('Algorithm not supported');
            default:
                throw new Exception('Unknown algorithm');
        }
        if (!$this->jwe_encrypted_key) {
            throw new Exception('Master key encryption failed');
        }
    }

    /**
    * The function decrypt the content base on the supported algorithm, copied from gree/jose package.
    * 
    * @return None 
    */
    private function decryptContentEncryptionKey($private_key_or_secret) {
        $this->generateContentEncryptionKey(null); # NOTE: run this always not to make timing difference
        $fake_content_encryption_key = $this->content_encryption_key;
        switch ($this->header['alg']) {
            case 'RSA1_5':
                $rsa = $this->rsa($private_key_or_secret, RSA::ENCRYPTION_PKCS1);
                $this->content_encryption_key = $rsa->decrypt($this->jwe_encrypted_key);
                break;
            case 'RSA-OAEP':
                $rsa = $this->rsa($private_key_or_secret, RSA::ENCRYPTION_OAEP);
                $this->content_encryption_key = $rsa->decrypt($this->jwe_encrypted_key);
                break;
            case 'dir':
                $this->content_encryption_key = $private_key_or_secret;
                break;
            case 'A128KW':
            case 'A256KW':
            case 'ECDH-ES':
            case 'ECDH-ES+A128KW':
            case 'ECDH-ES+A256KW':
                throw new Exception('Algorithm not supported');
            default:
                throw new Exception('Unknown algorithm');
        }
        if (!$this->content_encryption_key) {
            # NOTE:
            #  Not to disclose timing difference between CEK decryption error and others.
            #  Mitigating Bleichenbacher Attack on PKCS#1 v1.5
            #  ref.) http://inaz2.hatenablog.com/entry/2016/01/26/222303
            $this->content_encryption_key = $fake_content_encryption_key;
        }
    }

    /**
    * The function calculate the mac key and encryption key for the supported encryption header, copied from gree/jose package.
    * 
    * @return None 
    */
    private function deriveEncryptionAndMacKeys() {
        switch ($this->header['enc']) {
            case 'A128GCM':
            case 'A256GCM':
                $this->encryption_key = $this->content_encryption_key;
                $this->mac_key = "won't be used";
                break;
            case 'A128CBC-HS256':
                $this->deriveEncryptionAndMacKeysCBC(256);
                break;
            case 'A256CBC-HS512':
                $this->deriveEncryptionAndMacKeysCBC(512);
                break;
            default:
                throw new Exception('Unknown algorithm');
        }
        if (!$this->encryption_key || !$this->mac_key) {
            throw new Exception('Encryption/Mac key derivation failed');
        }
    }

    /**
     * The function calculate the mac key and encryption key, copied from gree/jose package.
     * 
     * @return None 
     */
    private function deriveEncryptionAndMacKeysCBC($sha_size) {
        $this->mac_key = substr($this->content_encryption_key, 0, $sha_size / 2 / 8);
        $this->encryption_key = substr($this->content_encryption_key, $sha_size / 2 / 8);
    }

    /**
     * The function encrypt a cipher text, copied from gree/jose package.
     * 
     * @return None 
     */
    private function encryptCipherText() {
        $dirSlash = $this->_getDirSlash();
        $hexIV = substr(bin2hex($this->iv), 0, 16);
        $hexEK = substr(bin2hex($this->encryption_key), 0, 32);

        $enc_output = null;
        $enc_retval = null;
        $script_path = __DIR__ . $dirSlash . "node_scripts" . $dirSlash . "cipher.js";
        exec("node {$script_path} -enc -p {$this->plain_text} -K {$hexEK} -iv {$hexIV}", $enc_output, $enc_retval);
        if ($enc_retval != 0) {
            throw new Exception("Payload encryption failed");
        }
        if(count($enc_output) > 0) {
            $this->cipher_text = $enc_output[0];
        }

    }

    /**
     * The function decrypt a cipher text, copied from gree/jose package.
     * 
     * @return None 
     */
    private function decryptCipherText() {
        $dirSlash = $this->_getDirSlash();
        $hexIV = substr(bin2hex($this->iv), 0, 16);
        $hexEK = substr(bin2hex($this->encryption_key), 0, 32);

        $dec_output = null;
        $dec_retval = null;
        $dec_script_path = __DIR__ . $dirSlash . "node_scripts" . $dirSlash . "cipher.js";
        
        exec("node {$dec_script_path} -dec -p {$this->cipher_text} -K {$hexEK} -iv {$hexIV}", $dec_output, $dec_retval);
        if ($dec_retval != 0) {
            throw new Exception("Payload decryption failed");
        }
        if(count($dec_output) > 0) {
            $this->plain_text = $dec_output[0];
        }
    }

    /**
     * The function generates the authenticat tag, copied from gree/jose package.
     * 
     * @return String 
     */
    private function generateAuthenticationTag() {
        $this->authentication_tag = $this->calculateAuthenticationTag();
    }

    /**
     * The function calculates the authenticat tag, copied from gree/jose package.
     * @param use_raw  boolean to check if using raw
     * 
     * @return String 
     */
    private function calculateAuthenticationTag($use_raw = false) {
        switch ($this->header['enc']) {
            case 'A128GCM':
            case 'A256GCM':
                throw new Exception('Algorithm not supported');
            case 'A128CBC-HS256':
                return $this->calculateAuthenticationTagCBC(256);
            case 'A256CBC-HS512':
                return $this->calculateAuthenticationTagCBC(512);
            default:
                throw new Exception('Unknown algorithm');
        }
    }

    /**
     * The function calculates the authenticat tag CBC, copied from gree/jose package.
     * @param sha_size  Size for hash
     * 
     * @return String 
     */
    private function calculateAuthenticationTagCBC($sha_size) {
        if (!$this->auth_data) {
            $this->auth_data = $this->compact((object) $this->header);
        }
        $auth_data_length = strlen($this->auth_data);
        $max_32bit = 2147483647;
        $secured_input = implode('', array(
            $this->auth_data,
            $this->iv,
            $this->cipher_text,
            // NOTE: PHP doesn't support 64bit big endian, so handling upper & lower 32bit.
            pack('N2', ($auth_data_length / $max_32bit) * 8, ($auth_data_length % $max_32bit) * 8)
        ));
        return substr(
            hash_hmac('sha' . $sha_size, $secured_input, $this->mac_key, true),
            0, $sha_size / 2 / 8
        );
    }

    /**
     * The function checks if authentication tag is equal, copied from gree/jose package.
     * 
     * @return Boolean 
     */
    private function checkAuthenticationTag() {
        if (hash_equals($this->authentication_tag, $this->calculateAuthenticationTag())) {
            return true;
        } else {
            throw new Exception('Invalid authentication tag');
        }
    }
}