<?php

define("HOSPITALS", array(
  "SAITAMA" => 1,
  "AIWA" => 2,
  "KEIAI" => 3,
  "SETO" => 4,
));

require dirname(__DIR__) . '/vendor/autoload.php';

require "HealthDataToken.php";
require "HealthDataQrCode.php";
require "Validator.php";
require "config/index.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;


class HealthDataManager {

  private $storage_path;
  private $token_instance;
  private $qrcode_instance;
  private $validator_instance;
  private $client;
  private $endpoint; 
  private $institution;

  public function __construct($config, $selected_institution) {
    if(((1 <= $selected_institution) && ($selected_institution <= 4))) {
      $this->token_instance = new HealthDataToken();
      $this->qrcode_instance = new HealthDataQrCode();
      $this->validator_instance = new Validator();
      $this->client = new Client();
      $this->endpoint = $config["API_URL"];
      $this->institution = $selected_institution;
    } else {
      throw new Exception('Invalid hospital id');
    }
   
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
    
      # Store Patient Public Key.
      $public_key_detail = openssl_pkey_get_details($keys);
      $public_key = $public_key_detail["key"];

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

    # Store Patient Public Key.
    $public_key_detail = openssl_pkey_get_details($keys);
    $public_key = $public_key_detail["key"];

    return array(
      'private_key' => $private_key,
      'public_key' => $public_key
    );
  }

  public function createEncKeyPair($user_id) {
    try {
      if(!$this->validator_instance->isValidUserID($user_id)) {
        throw new Exception('Invalid patient id');
      }
  
      // Generate Encryption Key Pairs
      $keys = $this->_generateJWEKeys();
  
      $request = $this->client->request('POST', $this->endpoint . "/qr-library/key-pair", [
        'form_params' => [
            'emr_patient_id' => $user_id,
            'pem_list' => $keys,
            'jose_type' => 'JWE',
            'institution_id' => $this->institution
        ]
      ]);
      return; 
    } catch (ServerException $e) {
      throw new Exception('Error Saving Encryption Key Pair');
    } 
  } 

  public function generateEncPrivateKeyQr($user_id) {
    if(!$this->validator_instance->isValidUserID($user_id)) {
      throw new Exception('Invalid patient id');
    }
    try {
      $request = $this->client->request('POST', $this->endpoint . "/qr-library/get-key-pair", [
        'form_params' => [
            'emr_patient_id' => $user_id,
            'jose_type' => 'JWE',
            'institution_id' => $this->institution
        ]
      ]);
      $response = $request->getBody();
      $result = json_decode($response);
      if($result->statusCode != 200) {
        throw new Exception('Keys not found');
      }
      $data = $result->data;
      $private_key = $data->pem_list[0]->private_key;
      $compressed_private_key = gzdeflate($private_key);
      $base64URLPK = $this->qrcode_instance->base64UrlEncode($compressed_private_key);
      
      $this->qrcode_instance->generateQrCode($base64URLPK, "./private-key.png");
    } catch (ServerException $e) {
      throw new Exception('Keys not found');
    } 
   
  }
}

$manager = new HealthDataManager($env, HOSPITALS["SAITAMA"]);

$manager->generateEncPrivateKeyQr("EMR-101");


