<?php

require "HealthDataToken.php";
require "HealthDataQrCode.php";
require "Validator.php";

class HealthDataManager {

  private $storage_path;
  private $token_instance;
  private $qrcode_instance;
  private $validator_instance;

  public function __construct() {
    $this->token_instance = new HealthDataToken();
    $this->qrcode_instance = new HealthDataQrCode();
    $this->validator_instance = new Validator();
  }

  private function _generateJWSKeys() {
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

  private function _generateJWEKeys() {
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

  private function _setStorageDirectory($user_id) {
    // NOTE: STILL IN CONFIRMATION. DEVS ARE STILL SUGGESTING TO USE DATABASE INSTEAD OF DIRECTORY.
    $dir_path = dirname(__FILE__).'/patient_keys'."/".$user_id;
    $folder_not_exists = !file_exists($dir_path);
    if ($folder_not_exists) {
      mkdir($dir_path, 0777, true);
    }
    $this->storage_path = $dir_path;
  }

  public function generateEncPrivateKeyQr($user_id) {
    if(!$this->validator_instance->isValidUserID($user_id)) {
      throw new Exception('Invalid patient id');
    }

    // Create Patient Cert Directory
    $this->_setStorageDirectory($user_id);
  
    // Generate Pair and Convert Private Key to QR Code
    return $this->_generateJWEKeys();
  } 
}

$manager = new HealthDataManager();

$manager->generateEncPrivateKeyQr("1");


