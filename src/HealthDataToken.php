<?php

namespace Saitama\QR;

use Exception;


require_once "HealthDataQrCode.php";
require_once "JWE.php";
class HealthDataToken
{

  private $qrcode_instance;

  /**
   * The constructor function initializes the class variables and creates instances of the other classes
   */
  public function __construct()
  {
    $this->qrcode_instance = new HealthDataQrCode();
  }

  /**
   * It creates a JWS token with the data provided and its private key for signature
   * 
   * @param kid The KID value of private key.
   * @param user_id The id of the user that will use this token.
   * @param signature_path The signature storage.
   * @param content The token payload or custom claims.
   * 
   * @return A JWS token
   */
  function createJWSToken($kid, $user_id, $signature_path, $content)
  {

    $dir_slash = stripos(PHP_OS, 'win') === 0 ? "\\" : "/";

    # Compressed Payload and Encode to Base64 
    $compressed = gzdeflate($content);
    $jws_payload_base64url = $this->qrcode_instance->base64UrlEncode($compressed);

    # Create Header and Encode to Base64
    $header = array(
      'alg' => 'RS256',
      'zip' => 'DEF',
      'kid' => $kid
    );
    $jws_header_base64url  = $this->qrcode_instance->base64UrlEncode(json_encode($header));

    # Concatenate Header and Payload
    $jws_header_payload = $jws_header_base64url . '.' . $jws_payload_base64url;


    # Create temporary folder and files for package read reference during generation.
    $user_file_temp = $signature_path . $dir_slash . "tmp" . $dir_slash . $user_id;
    $file_to_sign_path = $user_file_temp . $dir_slash . "_data_header_payload.txt";
    $signature_file_path = $user_file_temp . $dir_slash . "_signature_file_path.txt";
    if (!is_dir($user_file_temp)) {
      mkdir($user_file_temp, 0700, true);
    }

    file_put_contents($file_to_sign_path, $jws_header_payload);

    /*
    *
    * NOTE: Path for private key is specified below since the 
    * Openssl will read the private key pem file to generate a signature.  
    */

    # Get Private Key and KID
    $private_key_path = $signature_path . $dir_slash . "private_key.pem";

    $sign_output = null;
    $sign_retval = null;

    $base64_output = null;
    $base64_retval = null;

    exec("openssl dgst -sha256 -sign {$private_key_path} -out {$signature_file_path} {$file_to_sign_path}", $sign_output, $sign_retval);
    if ($sign_retval != 0) {
      $msg = is_array($sign_output) ? implode($sign_output) : $sign_output;
      throw new Exception("JWS Error: {$msg}");
    } else {
      exec("openssl base64 -in $signature_file_path", $base64_output, $base64_retval);
      if ($base64_retval != 0) {
        $msg = is_array($base64_output) ? implode($base64_output) : $base64_output;
        throw new Exception("JWS Error: {$msg}");
      }
      $sig = implode($base64_output);
      $sig_part = $this->qrcode_instance->base64UrlEncode($sig, false); // Already encoded by the latter exec command 
      $token = $jws_header_payload . '.' . $sig_part;
    }

    # Clean up temporary folder and files
    unlink($file_to_sign_path);
    unlink($signature_file_path);
    rmdir($user_file_temp);
    return $token;
  }

  /**
   * It creates a JWE token with the data provided and its public key for encryption.
   * 
   * @param public_key The user encryption key storage.
   * @param content The token payload or custom claims.
   * 
   * @return A JWE token
   */
  function createJWEToken($public_key, $content)
  {
    $token = "";

    $jwe = new JWE($content);
    $jwe->encrypt($public_key, 'RSA-OAEP', 'A256CBC-HS512');
    $token = $jwe->toString();
    return $token;
  }
}
