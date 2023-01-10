<?php

namespace Saitama\QR;

class URLSafeBase64 {
   /**
   * This function handles base64 encoding, copied from gree/jose package
   * 
   * @param input a string to encode
   * 
   * @return An encoded input.
   */
    static function encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * This function handles base64 decoding, copied from gree/jose package
     * 
     * @param input a string to decode
     * 
     * @return A dencoded input.
     */
    static function decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}