<?php

require_once './HealthDataQrCode.php';

class HealthDataManager {

  var $storagePath;

  function __construct(int $patient_id) {
    $dir_path = dirname(__FILE__).'/patient_keys'."/".$patient_id;
    $folder_not_exists = !file_exists($dir_path);
    if ($folder_not_exists) {
      mkdir($dir_path, 0777, true);
    }

    $this->storagePath = $dir_path;
  }

  public function generate_jws_key_pair() {
    # Generate a new private (and public) key pair.
    $config = array(
      "private_key_bits" => 2048,
      "private_key_type" => OPENSSL_KEYTYPE_RSA
    );
    $keys = openssl_pkey_new($config);
    
    # Store Patient Private Key.
    openssl_pkey_export($keys, $private_key);
    file_put_contents($this->storagePath . '/jws-private-key.pem', $private_key);
  
    # Store Patient Public Key.
    $public_key_detail = openssl_pkey_get_details($keys);
    $public_key = $public_key_detail["key"];
    file_put_contents($this->storagePath . '/jws-public-key.pem', $public_key);
  
    return array(
      'privateKey' => $private_key,
      'publicKey' => $public_key
    );
  }

}

$instance = new HealthDataManager(1);

$instance->generate_jws_key_pair();


