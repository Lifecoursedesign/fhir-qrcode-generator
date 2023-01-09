<?php

namespace Saitama\QR;

if (!strpos(__DIR__, 'vendor')) {
	include_once(__DIR__ . '/../../vendor/autoload.php');
}

require_once "URLSafeBase64.php";

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use phpseclib\Crypt\Hash;
use Exception;

class JWK {
    public $components = array();

   /**
   *  The constructor function initializes the components value.
   * 
   * @param components an associative array about the key
   * 
   * @return Nothing
   */

   public function __construct($components = array()) {
        if (!array_key_exists('kty', $components)) {
            throw new Exception('"kty" is required');
        }
        $this->components = $components;
        if (!array_key_exists('kid', $this->components)) {
            $this->components['kid'] = $this->thumbprint();
        }
    }

    /**
     *  This function converts the components value to key, copied from gree/jose package.
     * 
     * @return RSA key
     */
    public function toKey() {
        switch ($this->components['kty']) {
            case 'RSA':
                $rsa = new RSA();
                $n = new BigInteger('0x' . bin2hex(URLSafeBase64::decode($this->components['n'])), 16);
                $e = new BigInteger('0x' . bin2hex(URLSafeBase64::decode($this->components['e'])), 16);
                if (array_key_exists('d', $this->components)) {
                    throw new Exception('RSA private key isn\'t supported');
                } else {
                    $pem_string = $rsa->_convertPublicKey($n, $e);
                }
                $rsa->loadKey($pem_string);
                return $rsa;
            default:
                throw new Exception('Unknown key type');
        }
    }

    /**
     *  This function converts the components value to key, copied from gree/jose package.
     * @param hash_algorithm hash parameter
     * 
     * @return Encoded HMAC string
     */
    public function thumbprint($hash_algorithm = 'sha256') {
        $hash = new Hash($hash_algorithm);
        return URLSafeBase64::encode(
            $hash->hash(
                json_encode($this->normalized())
            )
        );
    }

    /**
     *  This function normalized RSA key, copied from gree/jose package.
     * 
     * @return Array of normalized key
     */
    private function normalized() {
        switch ($this->components['kty']) {
            case 'RSA':
                return array(
                    'e'   => $this->components['e'],
                    'kty' => $this->components['kty'],
                    'n'   => $this->components['n']
                );
            default:
                throw new Exception('Unknown key type');
        }
    }

    /**
     * The function encodes the components
     * 
     * @return String
     */
    function toString() {
        return json_encode($this->components);
    }

    /**
     * The function returns the encoded components
     * 
     * @return String
     */
    function __toString() {
        return $this->toString();
    }

    /**
     * The function encodes the key, copied from gree/jose package.
     * @param key to assign the components variable.
     * 
     * @param extra_components array holder for extra components
     * 
     * @return Array of components
     */
    static function encode($key, $extra_components = array()) {
        switch(get_class($key)) {
            case 'phpseclib\Crypt\RSA':
                $components = array(
                    'kty' => 'RSA',
                    'e' => URLSafeBase64::encode($key->publicExponent->toBytes()),
                    'n' => URLSafeBase64::encode($key->modulus->toBytes())
                );
                if ($key->exponent != $key->publicExponent) {
                    $components = array_merge($components, array(
                        'd' => URLSafeBase64::encode($key->exponent->toBytes())
                    ));
                }
                return new self(array_merge($components, $extra_components));
            default:
                throw new Exception('Unknown key type');
        }
    }

    /**
     * The function decode the components, copied from gree/jose package.
     * @param components array of components to decode.
     * 
     * @return RSA key
     */
    static function decode($components) {
        $jwk = new self($components);
        return $jwk->toKey();
    }
}