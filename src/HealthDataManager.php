<?php

require "HealthDataQrCode.php";

class HealthDataManager {

  private $storage_path;
  private $qr_code;

  public function __construct() {
    $this->qr_code = new HealthDataQrCode();
  }

  private function _generateJWSKeys(int $patient_id) {
      # Generate a new JWS private (and public) key pair.
      $config = array(
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA
      );
      $keys = openssl_pkey_new($config);

      # Store Patient Private Key.
      openssl_pkey_export($keys, $private_key);
      file_put_contents($this->storage_path . '/jws-private-key.pem', $private_key);
    
      # Store Patient Public Key.
      $public_key_detail = openssl_pkey_get_details($keys);
      $public_key = $public_key_detail["key"];
      file_put_contents($this->storage_path . '/jws-public-key.pem', $public_key);
      return array(
        'private_key' => $private_key,
        'public_key' => $public_key
      );
  }

  private function _generateJWEKeys(int $patient_id) {
     # Generate a new JWE private (and public) key pair.
    $config = array(
      "private_key_bits" => 2048,
      "private_key_type" => OPENSSL_KEYTYPE_RSA
    );
    $keys = openssl_pkey_new($config);

     # Store Patient Private Key.
    openssl_pkey_export($keys, $private_key);
    file_put_contents($this->storage_path . '/jwe-private-key.pem', $private_key);

    # Store Patient Public Key.
    $public_key_detail = openssl_pkey_get_details($keys);
    $public_key = $public_key_detail["key"];
    file_put_contents($this->storage_path . '/jwe-public-key.pem', $public_key);

    return array(
      'private_key' => $private_key,
      'public_key' => $public_key
    );
  }

  public function generateJWSandJWEKeyPairs($patient_id) {
    $valid_id = (is_int($patient_id) || ctype_digit($patient_id)) && (int)$patient_id > 0;
    if(!$valid_id) {
      throw new Exception('Invalid patient id');
    }

    // Create Patient Cert Directory
    $dir_path = dirname(__FILE__).'/patient_keys'."/".$patient_id;
    $folder_not_exists = !file_exists($dir_path);
    if ($folder_not_exists) {
      mkdir($dir_path, 0777, true);
    }
    $this->storage_path = $dir_path;

    // Start Generating Keys
    $jws_keys = $this->_generateJWSKeys($patient_id);
    $jwe_keys = $this->_generateJWEKeys($patient_id);

    // Convert Needed QR Codes
    $this->qr_code ->generateQrCode($jwe_keys["private_key"], $this->storage_path.'/jwe-private-key.png');
    $this->qr_code ->generateQrCode($jws_keys["public_key"], $this->storage_path.'/jws-public-key.png');
    
    return;
  }

}

$manager = new HealthDataManager();

$manager->generateJWSandJWEKeyPairs(1);


