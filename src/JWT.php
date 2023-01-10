<?php

namespace Saitama\QR;

use Exception;

require_once "URLSafeBase64.php";

class JWT {
    var $header = array(
        'typ' => 'JWT',
        'alg' => 'none'
    );
    var $claims = array();
    var $signature = '';
    var $raw;

    /**
     * The constructor function initializes the token Payload.
     * 
     * @param claims Payload for token
     */

    function __construct($claims = array()) {
        $this->claims = $claims;
    }

   /**
     * The function joins array element of segments and return a string token
     * 
     * @return Token
     */
    function toString() {
        return implode('.', array(
            $this->compact((object) $this->header),
            $this->compact((object) $this->claims),
            $this->compact($this->signature)
        ));
    }

    /**
     *  This function handles compacting token parts copied from gree/jose package
     * 
     * @return Token.
     */
    function __toString() {
        return $this->toString();
    }

  /**
   *  This function handles self encoding of JWT token, copied from gree/jose package
   * 
   * @param claims token payload
   * 
   * @return Token.
   */
    static function encode($claims) {
        return new self($claims);
    }

  /**
   *  This function handles compacting token parts, copied from gree/jose package
   * 
   * @param segment a string which is a part from token
   * @param as_binary a boolean flag to indicate whether it's a binary or not
   * 
   * @return Token extracted value.
   */
    protected function compact($segment) {
        if (is_object($segment)) {
            $stringified = str_replace("\/", "/", json_encode($segment));
        } else {
            $stringified = $segment;
        }
        if ($stringified === 'null' && $segment !== null) { // shouldn't happen, just for safe
            throw new Exception('Compact seriarization failed');
        }
        return URLSafeBase64::encode($stringified);
    }

   /**
   *  This function handles extraction of token, copied from gree/jose package
   * 
   * @param segment a string which is a part from token
   * @param as_binary a boolean flag to indicate whether it's a binary or not
   * 
   * @return Token extracted value.
   */
    protected function extract($segment, $as_binary = false) {
        $stringified = URLSafeBase64::decode($segment);
        if ($as_binary) {
            $extracted = $stringified;
        } else {
            $extracted = json_decode(strVal($stringified));
            if ($stringified !== 'null' && $extracted === null) {
                throw new Exception('Compact de-serialization failed');
            }
        }
        return $extracted;
    }
}

 // function sign($private_key_or_secret, $algorithm = 'HS256') {
    //     $jws = $this->toJWS();
    //     $jws->sign($private_key_or_secret, $algorithm);
    //     return $jws;
    // }

    // function verify($public_key_or_secret, $alg = null) {
    //     $jws = $this->toJWS();
    //     $jws->verify($public_key_or_secret, $alg);
    //     return $jws;
    // }

    // function encrypt($public_key_or_secret, $algorithm = 'RSA1_5', $encryption_method = 'A128CBC-HS256') {
    //     $jwe = new JOSE_JWE($this);
    //     $jwe->encrypt($public_key_or_secret, $algorithm, $encryption_method);
    //     return $jwe;
    // }

    // static function decode($jwt_string) {
    //     $segments = explode('.', $jwt_string);
    //     switch (count($segments)) {
    //         case 3:
    //             $jwt = new self();
    //             $jwt->raw = $jwt_string;
    //             $jwt->header = (array) $jwt->extract($segments[0]);
    //             $jwt->claims = (array) $jwt->extract($segments[1]);
    //             $jwt->signature      = $jwt->extract($segments[2], 'as_binary');
    //             return $jwt;
    //         case 5:
    //             $jwe = new JOSE_JWE($jwt_string);
    //             $jwe->auth_data  = $segments[0];
    //             $jwe->header     = (array) $jwe->extract($segments[0]);
    //             $jwe->jwe_encrypted_key  = $jwe->extract($segments[1], 'as_binary');
    //             $jwe->iv                 = $jwe->extract($segments[2], 'as_binary');
    //             $jwe->cipher_text        = $jwe->extract($segments[3], 'as_binary');
    //             $jwe->authentication_tag = $jwe->extract($segments[4], 'as_binary');
    //             return $jwe;
    //         default:
    //             throw new Exception('JWT should have exact 3 or 5 segments');
    //     }
    // }